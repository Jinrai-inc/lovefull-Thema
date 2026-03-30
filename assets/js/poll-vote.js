/**
 * 投票送信（Ajax）
 *
 * @package KoiRiaPortal
 */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.poll-card').forEach(card => {
        const pollId = card.dataset.pollId;
        if (!pollId) return;

        const options = card.querySelectorAll('.poll-option.is-votable');
        options.forEach(option => {
            option.addEventListener('click', async () => {
                const index = parseInt(option.dataset.index, 10);

                try {
                    const response = await fetch(`${koiRia.ajaxUrl}vote`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': koiRia.nonce,
                        },
                        body: JSON.stringify({
                            poll_id: parseInt(pollId, 10),
                            option_index: index,
                        }),
                    });

                    const data = await response.json();

                    if (data.success && data.results) {
                        // 結果表示に更新
                        options.forEach((opt, i) => {
                            opt.classList.remove('is-votable');
                            if (i === index) {
                                opt.classList.add('is-selected');
                            }

                            const result = data.results[i];
                            if (!result) return;

                            // パーセント表示追加
                            let pctEl = opt.querySelector('.poll-option__pct');
                            if (!pctEl) {
                                pctEl = document.createElement('span');
                                pctEl.className = 'poll-option__pct';
                                opt.appendChild(pctEl);
                            }
                            pctEl.textContent = result.pct + '%';

                            // バー追加
                            let barEl = opt.nextElementSibling;
                            if (!barEl || !barEl.classList.contains('poll-option__bar')) {
                                barEl = document.createElement('div');
                                barEl.className = 'poll-option__bar';
                                barEl.innerHTML = '<div class="poll-option__bar-fill"></div>';
                                opt.after(barEl);
                            }
                            const fill = barEl.querySelector('.poll-option__bar-fill');
                            if (fill) {
                                fill.style.width = result.pct + '%';
                            }
                        });
                    }
                } catch (err) {
                    console.error('投票エラー:', err);
                }
            });
        });
    });
});
