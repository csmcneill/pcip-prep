/**
 * PCIP Prep - Domain Quiz Engine
 */
(function () {
	'use strict';

	var sessionId    = null;
	var questions    = [];
	var currentIndex = 0;
	var correctCount = 0;
	var totalCount   = 0;
	var selectedDomain = '';
	var selectedReq    = null;

	// Screens.
	var domainSelect  = document.getElementById('pcip-prep-domain-select');
	var lengthSelect  = document.getElementById('pcip-prep-length-select');
	var quizInterface = document.getElementById('pcip-prep-interface');
	var quizResults   = document.getElementById('pcip-prep-results');

	if (!domainSelect) return;

	// Populate question counts.
	var counts = (typeof pcipPrepData !== 'undefined') ? pcipPrepData.domainCounts : {};
	document.querySelectorAll('[data-domain-count]').forEach(function (el) {
		var slug  = el.dataset.domainCount;
		var count = counts[slug] ? counts[slug].count : 0;
		el.textContent = count ? count + ' questions' : 'No questions yet';
	});

	// Domain card clicks.
	domainSelect.querySelectorAll('.pcip-domain-card').forEach(function (card) {
		card.addEventListener('click', function () {
			var domain = this.dataset.domain;

			if (this.classList.contains('pcip-domain-expandable')) {
				var reqGrid = document.getElementById('pcip-prep-requirements');
				reqGrid.style.display = reqGrid.style.display === 'none' ? 'grid' : 'none';
				return;
			}

			selectedDomain = domain;
			selectedReq = null;
			showLengthOptions(domain, null);
		});
	});

	// Requirement card clicks.
	domainSelect.querySelectorAll('.pcip-req-card').forEach(function (card) {
		card.addEventListener('click', function () {
			selectedDomain = this.dataset.domain;
			selectedReq = this.dataset.requirement || null;
			showLengthOptions(selectedDomain, selectedReq);
		});
	});

	function showLengthOptions(domain, requirement) {
		var slug      = requirement || domain;
		var available = counts[slug] ? counts[slug].count : 0;
		var name      = counts[slug] ? counts[slug].name : slug;

		if (available === 0) {
			alert('No questions available for this selection.');
			return;
		}

		document.getElementById('pcip-prep-domain-title').textContent = name;

		var container = document.getElementById('pcip-prep-length-options');
		container.innerHTML = '';

		var presets = [10, 25];
		presets.forEach(function (n) {
			if (n <= available) {
				var btn = document.createElement('button');
				btn.className = 'pcip-btn pcip-btn-primary pcip-length-btn';
				btn.textContent = n + ' Questions';
				btn.addEventListener('click', function () { startQuiz(n); });
				container.appendChild(btn);
			}
		});

		// "All" button.
		var allBtn = document.createElement('button');
		allBtn.className = 'pcip-btn pcip-btn-primary pcip-length-btn';
		allBtn.textContent = 'All (' + available + ')';
		allBtn.addEventListener('click', function () { startQuiz(0); });
		container.appendChild(allBtn);

		domainSelect.style.display = 'none';
		lengthSelect.style.display = 'block';
	}

	document.getElementById('pcip-prep-back-to-domains').addEventListener('click', function () {
		lengthSelect.style.display = 'none';
		domainSelect.style.display = 'block';
	});

	function startQuiz(count) {
		var body = {
			domain: selectedDomain,
			count:  count
		};
		if (selectedReq) {
			body.requirement = selectedReq;
		}

		fetch(pcipPrepData.restUrl + '/quiz/start', {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   pcipPrepData.nonce
			},
			body: JSON.stringify(body)
		})
		.then(function (res) { return res.json(); })
		.then(function (data) {
			sessionId    = data.session_id;
			questions    = data.questions;
			currentIndex = 0;
			correctCount = 0;
			totalCount   = data.total;

			lengthSelect.style.display  = 'none';
			quizInterface.style.display = 'block';
			showQuestion();
		});
	}

	function showQuestion() {
		var q = questions[currentIndex];

		// Progress.
		var num = currentIndex + 1;
		document.getElementById('pcip-prep-progress-text').textContent = 'Question ' + num + ' of ' + totalCount;
		var pct = (num / totalCount) * 100;
		document.getElementById('pcip-prep-progress-fill').style.width = pct + '%';

		// Question text.
		document.getElementById('pcip-prep-question-text').textContent = q.text;

		// Options.
		var optContainer = document.getElementById('pcip-prep-options');
		optContainer.innerHTML = '';

		q.options.forEach(function (opt) {
			var label = document.createElement('label');
			label.className = 'pcip-option-label';

			var input = document.createElement('input');
			input.type = 'radio';
			input.name = 'pcip-prep-answer';
			input.value = opt.key;
			input.addEventListener('change', function () {
				document.getElementById('pcip-prep-submit-answer').disabled = false;
			});

			var span = document.createElement('span');
			span.className = 'pcip-option-text';
			span.textContent = opt.text;

			label.appendChild(input);
			label.appendChild(span);
			optContainer.appendChild(label);
		});

		// Reset state.
		document.getElementById('pcip-prep-submit-answer').disabled = true;
		document.getElementById('pcip-prep-submit-answer').style.display = 'block';
		document.getElementById('pcip-prep-feedback').style.display = 'none';

		// Report button.
		var reportBtn = document.getElementById('pcip-prep-report-btn');
		if (reportBtn) {
			reportBtn.dataset.questionId = q.id;
		}
	}

	// Submit answer.
	document.getElementById('pcip-prep-submit-answer').addEventListener('click', function () {
		var selected = document.querySelector('input[name="pcip-prep-answer"]:checked');
		if (!selected) return;

		var q   = questions[currentIndex];
		var btn = this;
		btn.disabled = true;

		fetch(pcipPrepData.restUrl + '/quiz/answer', {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   pcipPrepData.nonce
			},
			body: JSON.stringify({
				session_id:      sessionId,
				question_number: q.number,
				selected_key:    selected.value
			})
		})
		.then(function (res) { return res.json(); })
		.then(function (data) {
			if (data.is_correct) {
				correctCount++;
			}

			// Highlight options.
			document.querySelectorAll('.pcip-option-label').forEach(function (label) {
				var input = label.querySelector('input');
				input.disabled = true;

				if (input.value === data.correct_key) {
					label.classList.add('pcip-option-correct');
				} else if (input.checked && !data.is_correct) {
					label.classList.add('pcip-option-incorrect');
				}
			});

			// Show feedback.
			btn.style.display = 'none';
			var feedback = document.getElementById('pcip-prep-feedback');
			feedback.style.display = 'block';

			var resultEl = document.getElementById('pcip-prep-feedback-result');
			resultEl.textContent = data.is_correct ? 'Correct!' : 'Incorrect';
			resultEl.className = 'pcip-feedback-result ' + (data.is_correct ? 'pcip-correct' : 'pcip-incorrect');

			document.getElementById('pcip-prep-explanation').textContent = data.explanation;

			// Change next button text on last question.
			var nextBtn = document.getElementById('pcip-prep-next');
			nextBtn.textContent = (currentIndex === totalCount - 1) ? 'See Results' : 'Next Question \u2192';
		});
	});

	// Next question.
	document.getElementById('pcip-prep-next').addEventListener('click', function () {
		if (currentIndex < totalCount - 1) {
			currentIndex++;
			showQuestion();
		} else {
			finishQuiz();
		}
	});

	function finishQuiz() {
		// Submit quiz to record session.
		fetch(pcipPrepData.restUrl + '/quiz/submit', {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   pcipPrepData.nonce
			},
			body: JSON.stringify({ session_id: sessionId })
		})
		.then(function (res) { return res.json(); })
		.then(function (data) {
			quizInterface.style.display = 'none';
			quizResults.style.display   = 'block';

			var pct = data.score_percent;
			document.getElementById('pcip-prep-score-percent').textContent = pct + '%';
			document.getElementById('pcip-prep-score-detail').textContent =
				data.correct + ' of ' + data.total + ' correct';

			var circle = document.getElementById('pcip-prep-score-circle');
			circle.className = 'pcip-score-circle ' + (pct >= 75 ? 'pcip-score-pass' : 'pcip-score-fail');
		});
	}

	// Results actions.
	document.getElementById('pcip-prep-try-again').addEventListener('click', function () {
		quizResults.style.display = 'none';
		showLengthOptions(selectedDomain, selectedReq);
	});

	document.getElementById('pcip-prep-back-to-select').addEventListener('click', function () {
		quizResults.style.display   = 'none';
		domainSelect.style.display  = 'block';
	});
})();
