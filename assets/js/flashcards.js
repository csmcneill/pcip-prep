/**
 * PCIP Prep - Flashcard Study Mode
 */
(function () {
	'use strict';

	var cards = [];
	var currentIndex = 0;

	// DOM refs.
	var domainSelect   = document.getElementById('pcip-fc-domain-select');
	var deck           = document.getElementById('pcip-fc-deck');
	var emptyState     = document.getElementById('pcip-fc-empty');
	var questionText   = document.getElementById('pcip-fc-question-text');
	var answerArea     = document.getElementById('pcip-fc-answer-area');
	var answerText     = document.getElementById('pcip-fc-answer-text');
	var reference      = document.getElementById('pcip-fc-reference');
	var revealBtn      = document.getElementById('pcip-fc-reveal');
	var prevBtn        = document.getElementById('pcip-fc-prev');
	var nextBtn        = document.getElementById('pcip-fc-next');
	var counter        = document.getElementById('pcip-fc-counter');
	var reportBtn      = document.getElementById('pcip-fc-report');
	var reqGrid        = document.getElementById('pcip-fc-requirements');

	if (!domainSelect) return;

	// Domain card clicks.
	domainSelect.querySelectorAll('.pcip-domain-card').forEach(function (card) {
		card.addEventListener('click', function () {
			var domain = this.dataset.domain;

			// Domain 3: expand requirements.
			if (this.classList.contains('pcip-domain-expandable')) {
				reqGrid.style.display = reqGrid.style.display === 'none' ? 'grid' : 'none';
				return;
			}

			loadFlashcards(domain, null);
		});
	});

	// Requirement card clicks.
	domainSelect.querySelectorAll('.pcip-req-card').forEach(function (card) {
		card.addEventListener('click', function () {
			var domain = this.dataset.domain;
			var req    = this.dataset.requirement || null;
			loadFlashcards(domain, req);
		});
	});

	function loadFlashcards(domain, requirement) {
		var url = pcipFlashcardData.restUrl + '/questions?type=flashcard&domain=' + encodeURIComponent(domain);
		if (requirement) {
			url += '&requirement=' + encodeURIComponent(requirement);
		}

		fetch(url, {
			headers: { 'X-WP-Nonce': pcipFlashcardData.nonce }
		})
		.then(function (res) { return res.json(); })
		.then(function (data) {
			if (!data.length) {
				domainSelect.style.display = 'none';
				emptyState.style.display = 'block';
				return;
			}

			cards = data;
			currentIndex = 0;
			domainSelect.style.display = 'none';
			deck.style.display = 'block';
			showCard();
		});
	}

	function showCard() {
		var card = cards[currentIndex];
		questionText.textContent = card.text;
		answerText.textContent = card.answer;
		reference.textContent = card.reference || '';
		answerArea.style.display = 'none';
		revealBtn.style.display = 'block';
		counter.textContent = 'Card ' + (currentIndex + 1) + ' of ' + cards.length;
		reportBtn.dataset.questionId = card.id;

		prevBtn.disabled = currentIndex === 0;
		nextBtn.disabled = currentIndex === cards.length - 1;
	}

	revealBtn.addEventListener('click', function () {
		answerArea.style.display = 'block';
		revealBtn.style.display = 'none';
	});

	prevBtn.addEventListener('click', function () {
		if (currentIndex > 0) {
			currentIndex--;
			showCard();
		}
	});

	nextBtn.addEventListener('click', function () {
		if (currentIndex < cards.length - 1) {
			currentIndex++;
			showCard();
		}
	});

	// Back buttons.
	document.getElementById('pcip-fc-back').addEventListener('click', function () {
		deck.style.display = 'none';
		domainSelect.style.display = 'block';
	});

	document.getElementById('pcip-fc-empty-back').addEventListener('click', function () {
		emptyState.style.display = 'none';
		domainSelect.style.display = 'block';
	});

	// Keyboard navigation.
	document.addEventListener('keydown', function (e) {
		if (deck.style.display === 'none') return;

		if (e.key === 'ArrowRight' || e.key === 'n') {
			nextBtn.click();
		} else if (e.key === 'ArrowLeft' || e.key === 'p') {
			prevBtn.click();
		} else if (e.key === ' ' || e.key === 'Enter') {
			if (answerArea.style.display === 'none') {
				e.preventDefault();
				revealBtn.click();
			}
		}
	});
})();
