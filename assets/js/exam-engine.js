/**
 * PCIP Prep - Full PCIP Exam Simulator
 */
(function () {
	'use strict';

	// State.
	var sessionId   = null;
	var questions   = [];
	var answers     = {};  // { questionNumber: selectedKey }
	var flags       = {};  // { questionNumber: true }
	var currentNum  = 1;
	var startTime   = null;
	var duration    = 90 * 60 * 1000; // 90 min in ms
	var timerHandle = null;
	var autosaveHandle = null;
	var STORAGE_KEY = '';

	// Screens.
	var splash    = document.getElementById('pcip-exam-splash');
	var examUI    = document.getElementById('pcip-exam-interface');
	var reviewUI  = document.getElementById('pcip-exam-review');
	var resultsUI = document.getElementById('pcip-exam-results');

	if (!splash) return;

	// Check if enough questions.
	if (typeof pcipExamData !== 'undefined' && pcipExamData.totalAvailable < pcipExamData.examSize) {
		document.getElementById('pcip-exam-insufficient').style.display = 'block';
		document.getElementById('pcip-exam-begin').disabled = true;
	}

	// Begin exam.
	document.getElementById('pcip-exam-begin').addEventListener('click', function () {
		this.disabled = true;
		this.textContent = 'Loading...';

		fetch(pcipExamData.restUrl + '/exam/start', {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   pcipExamData.nonce
			},
			body: JSON.stringify({})
		})
		.then(function (res) { return res.json(); })
		.then(function (data) {
			if (data.has_active_exam) {
				if (confirm('You have an active exam in progress. Start a new one? (The old one will be abandoned.)')) {
					return fetch(pcipExamData.restUrl + '/exam/start', {
						method:  'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-WP-Nonce':   pcipExamData.nonce
						},
						body: JSON.stringify({ force: true })
					}).then(function (res) { return res.json(); });
				}
				document.getElementById('pcip-exam-begin').disabled = false;
				document.getElementById('pcip-exam-begin').textContent = 'Begin Exam';
				return null;
			}
			return data;
		})
		.then(function (data) {
			if (!data) return;
			initExam(data);
		});
	});

	function initExam(data) {
		sessionId  = data.session_id;
		questions  = data.questions;
		answers    = {};
		flags      = {};
		currentNum = 1;
		startTime  = Date.now();
		STORAGE_KEY = 'pcip_exam_' + sessionId;

		// Check localStorage for resumed state.
		var saved = localStorage.getItem(STORAGE_KEY);
		if (saved) {
			try {
				var parsed = JSON.parse(saved);
				answers   = parsed.answers || {};
				flags     = parsed.flags || {};
				startTime = parsed.startTime || startTime;
			} catch (e) { /* ignore */ }
		}

		saveToLocal();

		// Build question grid.
		buildGrid();

		// Show exam UI.
		splash.style.display = 'none';
		examUI.style.display = 'block';

		document.getElementById('pcip-exam-total-num').textContent = questions.length;
		showQuestion(1);
		startTimer();
		startAutosave();

		// Warn on leave.
		window.addEventListener('beforeunload', onBeforeUnload);
	}

	function onBeforeUnload(e) {
		e.preventDefault();
		e.returnValue = '';
	}

	// ------------------------------------------------------------------
	// Timer
	// ------------------------------------------------------------------

	function startTimer() {
		updateTimer();
		timerHandle = setInterval(updateTimer, 1000);
	}

	function updateTimer() {
		var elapsed   = Date.now() - startTime;
		var remaining = Math.max(0, duration - elapsed);

		if (remaining === 0) {
			clearInterval(timerHandle);
			clearInterval(autosaveHandle);
			autoSubmit();
			return;
		}

		var totalSec = Math.floor(remaining / 1000);
		var min      = Math.floor(totalSec / 60);
		var sec      = totalSec % 60;
		var display  = min + ':' + (sec < 10 ? '0' : '') + sec;

		var timerEl = document.getElementById('pcip-exam-timer');
		timerEl.textContent = display;
		timerEl.classList.toggle('pcip-timer-warning', remaining < 300000); // < 5 min
	}

	// ------------------------------------------------------------------
	// Autosave
	// ------------------------------------------------------------------

	function startAutosave() {
		autosaveHandle = setInterval(function () {
			saveToServer();
		}, 30000);
	}

	function saveToLocal() {
		localStorage.setItem(STORAGE_KEY, JSON.stringify({
			answers:   answers,
			flags:     flags,
			startTime: startTime
		}));
	}

	function saveToServer() {
		fetch(pcipExamData.restUrl + '/exam/autosave', {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   pcipExamData.nonce
			},
			body: JSON.stringify({
				session_id: sessionId,
				answers:    answers,
				flags:      Object.keys(flags).filter(function (k) { return flags[k]; })
			})
		}).catch(function () { /* silent fail, localStorage is backup */ });
	}

	// ------------------------------------------------------------------
	// Question Grid
	// ------------------------------------------------------------------

	function buildGrid() {
		var grid = document.getElementById('pcip-exam-grid');
		grid.innerHTML = '';

		for (var i = 1; i <= questions.length; i++) {
			var btn = document.createElement('button');
			btn.className = 'pcip-grid-btn';
			btn.textContent = i;
			btn.dataset.num = i;
			btn.addEventListener('click', function () {
				showQuestion(parseInt(this.dataset.num));
			});
			grid.appendChild(btn);
		}
	}

	function updateGrid() {
		var btns = document.querySelectorAll('#pcip-exam-grid .pcip-grid-btn');
		var answeredCount = 0;

		btns.forEach(function (btn) {
			var num = parseInt(btn.dataset.num);
			btn.className = 'pcip-grid-btn';

			if (num === currentNum) {
				btn.classList.add('pcip-grid-current');
			}
			if (flags[num]) {
				btn.classList.add('pcip-grid-flagged');
			}
			if (answers[num]) {
				btn.classList.add('pcip-grid-answered');
				answeredCount++;
			}
		});

		document.getElementById('pcip-exam-answered-count').textContent =
			answeredCount + ' / ' + questions.length + ' answered';
	}

	// ------------------------------------------------------------------
	// Question Display
	// ------------------------------------------------------------------

	function showQuestion(num) {
		currentNum = num;
		var q = questions[num - 1];

		document.getElementById('pcip-exam-current-num').textContent = num;
		document.getElementById('pcip-exam-question-text').textContent = q.text;

		// Options.
		var optContainer = document.getElementById('pcip-exam-options');
		optContainer.innerHTML = '';

		q.options.forEach(function (opt) {
			var label = document.createElement('label');
			label.className = 'pcip-option-label';

			var input = document.createElement('input');
			input.type  = 'radio';
			input.name  = 'pcip-exam-answer';
			input.value = opt.key;

			if (answers[num] === opt.key) {
				input.checked = true;
			}

			input.addEventListener('change', function () {
				answers[num] = this.value;
				saveToLocal();
				updateGrid();
			});

			var span = document.createElement('span');
			span.className = 'pcip-option-text';
			span.textContent = opt.text;

			label.appendChild(input);
			label.appendChild(span);
			optContainer.appendChild(label);
		});

		// Flag state.
		updateFlagButton();
		updateGrid();

		// Report button.
		var reportBtn = document.getElementById('pcip-exam-report-btn');
		if (reportBtn) {
			reportBtn.dataset.questionId = q.id;
		}
	}

	// ------------------------------------------------------------------
	// Flag
	// ------------------------------------------------------------------

	document.getElementById('pcip-exam-flag').addEventListener('click', function () {
		flags[currentNum] = !flags[currentNum];
		updateFlagButton();
		updateGrid();
		saveToLocal();
	});

	function updateFlagButton() {
		var flagged = !!flags[currentNum];
		var btn     = document.getElementById('pcip-exam-flag');
		var text    = document.getElementById('pcip-exam-flag-text');

		btn.classList.toggle('pcip-flagged', flagged);
		text.textContent = flagged ? 'Unflag' : 'Flag for Review';
	}

	// ------------------------------------------------------------------
	// Navigation
	// ------------------------------------------------------------------

	document.getElementById('pcip-exam-prev').addEventListener('click', function () {
		if (currentNum > 1) showQuestion(currentNum - 1);
	});

	document.getElementById('pcip-exam-next').addEventListener('click', function () {
		if (currentNum < questions.length) showQuestion(currentNum + 1);
	});

	// Keyboard nav.
	document.addEventListener('keydown', function (e) {
		if (examUI.style.display === 'none') return;
		if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') return;

		if (e.key === 'ArrowRight') {
			if (currentNum < questions.length) showQuestion(currentNum + 1);
		} else if (e.key === 'ArrowLeft') {
			if (currentNum > 1) showQuestion(currentNum - 1);
		} else if (e.key === 'f') {
			document.getElementById('pcip-exam-flag').click();
		}
	});

	// Sidebar toggle (mobile).
	document.getElementById('pcip-exam-sidebar-toggle').addEventListener('click', function () {
		document.querySelector('.pcip-exam-sidebar').classList.toggle('pcip-sidebar-open');
	});

	// ------------------------------------------------------------------
	// Review Screen
	// ------------------------------------------------------------------

	document.getElementById('pcip-exam-review-btn').addEventListener('click', showReview);

	function showReview() {
		examUI.style.display  = 'none';
		reviewUI.style.display = 'block';

		var answered   = 0;
		var unanswered = 0;
		var flagged    = 0;

		for (var i = 1; i <= questions.length; i++) {
			if (answers[i]) answered++;
			else unanswered++;
			if (flags[i]) flagged++;
		}

		document.getElementById('pcip-review-answered').textContent   = answered;
		document.getElementById('pcip-review-unanswered').textContent = unanswered;
		document.getElementById('pcip-review-flagged').textContent    = flagged;

		// Build review grid.
		var grid = document.getElementById('pcip-review-grid');
		grid.innerHTML = '';

		for (var i = 1; i <= questions.length; i++) {
			var btn = document.createElement('button');
			btn.className = 'pcip-grid-btn';
			btn.textContent = i;
			btn.dataset.num = i;

			if (answers[i]) btn.classList.add('pcip-grid-answered');
			if (flags[i])   btn.classList.add('pcip-grid-flagged');

			btn.addEventListener('click', function () {
				reviewUI.style.display = 'none';
				examUI.style.display   = 'block';
				showQuestion(parseInt(this.dataset.num));
			});

			grid.appendChild(btn);
		}
	}

	document.getElementById('pcip-review-back').addEventListener('click', function () {
		reviewUI.style.display = 'none';
		examUI.style.display   = 'block';
	});

	document.getElementById('pcip-review-submit').addEventListener('click', function () {
		if (!confirm('Are you sure you want to submit your exam? This cannot be undone.')) return;
		submitExam();
	});

	// ------------------------------------------------------------------
	// Submission
	// ------------------------------------------------------------------

	function autoSubmit() {
		window.removeEventListener('beforeunload', onBeforeUnload);
		submitExam();
	}

	function submitExam() {
		clearInterval(timerHandle);
		clearInterval(autosaveHandle);
		window.removeEventListener('beforeunload', onBeforeUnload);

		// Show loading.
		reviewUI.style.display = 'none';
		examUI.style.display   = 'none';

		fetch(pcipExamData.restUrl + '/exam/submit', {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   pcipExamData.nonce
			},
			body: JSON.stringify({
				session_id: sessionId,
				answers:    answers
			})
		})
		.then(function (res) { return res.json(); })
		.then(function (data) {
			localStorage.removeItem(STORAGE_KEY);
			showResults(data);
		})
		.catch(function () {
			alert('Failed to submit exam. Your answers have been saved locally.');
		});
	}

	// ------------------------------------------------------------------
	// Results
	// ------------------------------------------------------------------

	function showResults(data) {
		resultsUI.style.display = 'block';

		// Pass/fail banner.
		var banner = document.getElementById('pcip-exam-result-banner');
		var text   = document.getElementById('pcip-exam-result-text');

		if (data.passed) {
			banner.className = 'pcip-result-banner pcip-result-pass';
			text.textContent  = 'PASSED';
		} else {
			banner.className = 'pcip-result-banner pcip-result-fail';
			text.textContent  = 'DID NOT PASS';
		}

		// Score.
		document.getElementById('pcip-exam-score-percent').textContent = data.score_percent + '%';
		document.getElementById('pcip-exam-score-detail').textContent  =
			data.correct + ' of ' + data.total + ' correct';

		var circle = document.getElementById('pcip-exam-score-circle');
		circle.className = 'pcip-score-circle ' + (data.passed ? 'pcip-score-pass' : 'pcip-score-fail');

		// Time.
		var min = Math.floor(data.time_spent / 60);
		var sec = data.time_spent % 60;
		document.getElementById('pcip-exam-time-spent').textContent =
			'Completed in ' + min + ':' + (sec < 10 ? '0' : '') + sec;

		// Domain breakdown.
		var domainNames = {
			'domain-1': 'Domain 1: PCI Essentials',
			'domain-2': 'Domain 2: PCI DSS Overview',
			'domain-3': 'Domain 3: PCI DSS Requirements',
			'domain-4': 'Domain 4: Reporting Fundamentals',
			'domain-5': 'Domain 5: SAQ Reporting'
		};

		var domainRows = document.getElementById('pcip-exam-domain-rows');
		domainRows.innerHTML = '';

		Object.keys(domainNames).forEach(function (key) {
			var stat = data.domain_breakdown[key];
			if (!stat) return;

			var tr = document.createElement('tr');
			var pct = stat.accuracy;
			tr.className = pct < 75 ? 'pcip-row-weak' : '';

			tr.innerHTML =
				'<td>' + domainNames[key] + '</td>' +
				'<td>' + stat.correct + '</td>' +
				'<td>' + stat.total + '</td>' +
				'<td>' + pct + '%</td>';
			domainRows.appendChild(tr);
		});

		// Requirement breakdown.
		if (data.requirement_breakdown && Object.keys(data.requirement_breakdown).length > 0) {
			document.getElementById('pcip-exam-req-section').style.display = 'block';
			var reqRows = document.getElementById('pcip-exam-req-rows');
			reqRows.innerHTML = '';

			for (var i = 1; i <= 12; i++) {
				var reqKey = 'requirement-' + i;
				var rStat  = data.requirement_breakdown[reqKey];
				if (!rStat) continue;

				var tr  = document.createElement('tr');
				var pct = rStat.accuracy;
				tr.className = pct < 75 ? 'pcip-row-weak' : '';

				tr.innerHTML =
					'<td>Requirement ' + i + '</td>' +
					'<td>' + rStat.correct + '</td>' +
					'<td>' + rStat.total + '</td>' +
					'<td>' + pct + '%</td>';
				reqRows.appendChild(tr);
			}
		}

		// Store details for answer review.
		resultsUI._details = data.details;
	}

	// Review answers button.
	document.getElementById('pcip-exam-review-answers').addEventListener('click', function () {
		var section = document.getElementById('pcip-exam-answer-review');
		if (section.style.display !== 'none') {
			section.style.display = 'none';
			return;
		}

		section.style.display = 'block';
		var container = document.getElementById('pcip-exam-answer-list');
		container.innerHTML = '';

		var details = resultsUI._details || {};
		Object.keys(details).sort(function (a, b) { return parseInt(a) - parseInt(b); }).forEach(function (num) {
			var d   = details[num];
			var div = document.createElement('div');
			div.className = 'pcip-answer-review-item ' + (d.is_correct ? 'pcip-review-correct' : 'pcip-review-incorrect');

			var html = '<p class="pcip-review-q"><strong>Q' + num + ':</strong> ' + escHtml(d.question_text) + '</p>';

			Object.keys(d.options).forEach(function (key) {
				var optText = d.options[key];
				var marker  = '';
				if (key === d.correct_key) marker = ' \u2705';
				if (key === d.selected_key && !d.is_correct) marker = ' \u274C';
				html += '<p class="pcip-review-opt">' + escHtml(optText) + marker + '</p>';
			});

			if (!d.is_correct) {
				html += '<p class="pcip-review-explanation"><em>' + escHtml(d.explanation) + '</em></p>';
			}

			div.innerHTML = html;
			container.appendChild(div);
		});
	});

	// Take another exam.
	document.getElementById('pcip-exam-take-another').addEventListener('click', function () {
		resultsUI.style.display = 'none';
		splash.style.display    = 'block';
		document.getElementById('pcip-exam-begin').disabled = false;
		document.getElementById('pcip-exam-begin').textContent = 'Begin Exam';
	});

	function escHtml(str) {
		var div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}
})();
