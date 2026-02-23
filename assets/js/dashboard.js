/**
 * PCIP Prep - Performance Dashboard
 */
(function () {
	'use strict';

	var loading = document.getElementById('pcip-dash-loading');
	var empty   = document.getElementById('pcip-dash-empty');
	var content = document.getElementById('pcip-dash-content');

	if (!loading) return;

	var domainNames = {
		'domain-1': 'Domain 1: PCI Essentials',
		'domain-2': 'Domain 2: PCI DSS Overview',
		'domain-3': 'Domain 3: PCI DSS Requirements',
		'domain-4': 'Domain 4: Reporting Fundamentals',
		'domain-5': 'Domain 5: SAQ Reporting'
	};

	fetch(pcipDashboardData.restUrl + '/dashboard', {
		headers: { 'X-WP-Nonce': pcipDashboardData.nonce }
	})
	.then(function (res) { return res.json(); })
	.then(function (data) {
		loading.style.display = 'none';

		if (!data.stats || data.stats.total_answered === 0) {
			empty.style.display = 'block';
			return;
		}

		content.style.display = 'block';
		renderOverview(data.stats);
		renderDomainBars(data.domain_stats);
		renderRequirementBars(data.requirement_stats);
		renderWeakAreas(data.domain_stats, data.requirement_stats);
		renderExamHistory(data.exam_history);
		renderQuizHistory(data.quiz_history);
	})
	.catch(function () {
		loading.textContent = 'Failed to load dashboard data.';
	});

	function renderOverview(stats) {
		document.getElementById('pcip-dash-total-quizzes').textContent  = stats.total_quizzes;
		document.getElementById('pcip-dash-total-answered').textContent = stats.total_answered;
		document.getElementById('pcip-dash-accuracy').textContent       = stats.accuracy + '%';
		document.getElementById('pcip-dash-exam-attempts').textContent  = stats.exam_attempts;
		document.getElementById('pcip-dash-best-exam').textContent      = stats.best_exam_score !== null ? stats.best_exam_score + '%' : '--';
	}

	function renderDomainBars(domainStats) {
		var container = document.getElementById('pcip-dash-domain-bars');
		container.innerHTML = '';

		Object.keys(domainNames).forEach(function (key) {
			var stat = domainStats[key];
			if (!stat) return;

			container.appendChild(createBar(domainNames[key], stat.accuracy, stat.correct + '/' + stat.total));
		});
	}

	function renderRequirementBars(reqStats) {
		if (!reqStats || Object.keys(reqStats).length === 0) return;

		document.getElementById('pcip-dash-req-section').style.display = 'block';
		var container = document.getElementById('pcip-dash-req-bars');
		container.innerHTML = '';

		for (var i = 1; i <= 12; i++) {
			var key  = 'requirement-' + i;
			var stat = reqStats[key];
			if (!stat) continue;

			container.appendChild(createBar('Requirement ' + i, stat.accuracy, stat.correct + '/' + stat.total));
		}
	}

	function createBar(label, pct, detail) {
		var div = document.createElement('div');
		div.className = 'pcip-dash-bar-item';

		var barClass = pct >= 75 ? 'pcip-bar-pass' : 'pcip-bar-fail';

		div.innerHTML =
			'<div class="pcip-dash-bar-label">' +
			'<span>' + escHtml(label) + '</span>' +
			'<span class="pcip-dash-bar-detail">' + escHtml(detail) + ' (' + pct + '%)</span>' +
			'</div>' +
			'<div class="pcip-dash-bar-track">' +
			'<div class="pcip-dash-bar-fill ' + barClass + '" style="width:' + pct + '%"></div>' +
			'</div>';

		return div;
	}

	function renderWeakAreas(domainStats, reqStats) {
		var weakItems = [];

		Object.keys(domainNames).forEach(function (key) {
			var stat = domainStats[key];
			if (stat && stat.accuracy < 75) {
				weakItems.push(domainNames[key] + ' (' + stat.accuracy + '%)');
			}
		});

		for (var i = 1; i <= 12; i++) {
			var key  = 'requirement-' + i;
			var stat = reqStats[key];
			if (stat && stat.accuracy < 75) {
				weakItems.push('Requirement ' + i + ' (' + stat.accuracy + '%)');
			}
		}

		if (weakItems.length === 0) return;

		document.getElementById('pcip-dash-weak-section').style.display = 'block';
		var container = document.getElementById('pcip-dash-weak-list');
		container.innerHTML = '';

		weakItems.forEach(function (item) {
			var div = document.createElement('div');
			div.className = 'pcip-dash-weak-item';
			div.textContent = item;
			container.appendChild(div);
		});
	}

	function renderExamHistory(exams) {
		if (!exams || exams.length === 0) return;

		document.getElementById('pcip-dash-exam-history').style.display = 'block';
		var tbody = document.getElementById('pcip-dash-exam-rows');
		tbody.innerHTML = '';

		exams.forEach(function (exam) {
			var tr = document.createElement('tr');
			var min = Math.floor((exam.time_spent_seconds || 0) / 60);

			tr.innerHTML =
				'<td>' + formatDate(exam.completed_at) + '</td>' +
				'<td>' + parseFloat(exam.score_percent).toFixed(1) + '%</td>' +
				'<td><span class="pcip-status-badge ' + (parseInt(exam.passed) ? 'pcip-status-pass' : 'pcip-status-fail') + '">' +
				(parseInt(exam.passed) ? 'Passed' : 'Failed') + '</span></td>' +
				'<td>' + min + ' min</td>';

			tbody.appendChild(tr);
		});
	}

	function renderQuizHistory(quizzes) {
		if (!quizzes || quizzes.length === 0) return;

		document.getElementById('pcip-dash-quiz-history').style.display = 'block';
		var tbody = document.getElementById('pcip-dash-quiz-rows');
		tbody.innerHTML = '';

		quizzes.forEach(function (quiz) {
			var tr = document.createElement('tr');
			var domainLabel = domainNames[quiz.domain] || quiz.domain || 'Mixed';

			tr.innerHTML =
				'<td>' + formatDate(quiz.completed_at) + '</td>' +
				'<td>' + escHtml(domainLabel) + '</td>' +
				'<td>' + parseFloat(quiz.score_percent).toFixed(1) + '%</td>';

			tbody.appendChild(tr);
		});
	}

	function formatDate(dateStr) {
		if (!dateStr) return '';
		var d = new Date(dateStr);
		return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
	}

	function escHtml(str) {
		var div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}
})();
