/**
 * PCIP Prep - Issue Report Modal
 */
(function () {
	'use strict';

	var modal      = document.getElementById('pcip-issue-modal');
	var form       = document.getElementById('pcip-issue-form');
	var success    = document.getElementById('pcip-issue-success');
	var qIdInput   = document.getElementById('pcip-issue-question-id');

	if (!modal) return;

	// Open modal from any "Report Issue" button.
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.pcip-report-issue-btn');
		if (!btn) return;

		e.preventDefault();
		var questionId = btn.dataset.questionId;
		if (!questionId) return;

		qIdInput.value = questionId;
		form.style.display    = 'block';
		success.style.display = 'none';
		form.reset();
		qIdInput.value = questionId;
		modal.style.display = 'flex';
	});

	// Close modal.
	function closeModal() {
		modal.style.display = 'none';
	}

	document.getElementById('pcip-issue-close').addEventListener('click', closeModal);
	document.getElementById('pcip-issue-cancel').addEventListener('click', closeModal);
	document.getElementById('pcip-issue-done').addEventListener('click', closeModal);

	modal.querySelector('.pcip-modal-backdrop').addEventListener('click', closeModal);

	// Submit form.
	form.addEventListener('submit', function (e) {
		e.preventDefault();

		var data = {
			question_id: parseInt(qIdInput.value),
			description: document.getElementById('pcip-issue-description').value,
			suggestion:  document.getElementById('pcip-issue-suggestion').value
		};

		fetch(pcipIssueData.restUrl + '/report-issue', {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   pcipIssueData.nonce
			},
			body: JSON.stringify(data)
		})
		.then(function (res) { return res.json(); })
		.then(function (result) {
			if (result.success) {
				form.style.display    = 'none';
				success.style.display = 'block';
			} else {
				alert('Failed to submit report. Please try again.');
			}
		})
		.catch(function () {
			alert('Failed to submit report. Please try again.');
		});
	});

	// Close on Escape.
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && modal.style.display !== 'none') {
			closeModal();
		}
	});
})();
