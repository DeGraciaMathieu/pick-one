<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('vote.question') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0a0a12;
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 640px;
            padding: 40px 24px;
        }

        h1 {
            text-align: center;
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 48px;
            letter-spacing: -0.5px;
            line-height: 1.3;
        }

        /* Vote buttons */
        .choices {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 48px;
        }

        .choice-btn {
            position: relative;
            padding: 28px 20px;
            border: 2px solid #2a2a40;
            border-radius: 16px;
            background: #12121e;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            font-family: inherit;
        }

        .choice-btn:hover:not(:disabled) {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .choice-btn:disabled {
            cursor: default;
            opacity: 0.6;
        }

        .choice-btn.voted {
            opacity: 1 !important;
        }

        .choice-btn[data-choice="a"] { --accent: {{ config('vote.option_a.color') }}; }
        .choice-btn[data-choice="b"] { --accent: {{ config('vote.option_b.color') }}; }

        .choice-btn:hover:not(:disabled),
        .choice-btn.voted {
            border-color: var(--accent);
            box-shadow: 0 0 30px color-mix(in srgb, var(--accent) 20%, transparent);
        }

        .choice-label {
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }

        .choice-desc {
            font-size: 0.85rem;
            color: #888;
        }

        .choice-btn.voted .choice-label {
            color: var(--accent);
        }

        .check-icon {
            display: none;
            position: absolute;
            top: 12px;
            right: 12px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            font-size: 14px;
            align-items: center;
            justify-content: center;
        }

        .choice-btn.voted .check-icon {
            display: flex;
        }

        /* Gauge */
        .gauge-section {
            margin-bottom: 24px;
        }

        .gauge-labels {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.85rem;
        }

        .gauge-label-a { color: {{ config('vote.option_a.color') }}; font-weight: 600; }
        .gauge-label-b { color: {{ config('vote.option_b.color') }}; font-weight: 600; }

        .gauge-track {
            width: 100%;
            height: 14px;
            background: #1a1a2e;
            border-radius: 7px;
            overflow: hidden;
            display: flex;
            border: 1px solid #2a2a40;
        }

        .gauge-fill-a {
            height: 100%;
            background: {{ config('vote.option_a.color') }};
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            width: 50%;
            border-radius: 7px 0 0 7px;
        }

        .gauge-fill-b {
            height: 100%;
            background: {{ config('vote.option_b.color') }};
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            width: 50%;
            border-radius: 0 7px 7px 0;
        }

        /* Percentages */
        .gauge-percentages {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .pct {
            font-size: 2rem;
            font-weight: 800;
            transition: color 0.3s;
        }

        .pct-a { color: {{ config('vote.option_a.color') }}; }
        .pct-b { color: {{ config('vote.option_b.color') }}; }

        /* Total */
        .total {
            text-align: center;
            margin-top: 32px;
            color: #555;
            font-size: 0.85rem;
        }

        .total span {
            color: #888;
            font-weight: 600;
        }

        /* Message */
        .message {
            text-align: center;
            margin-bottom: 24px;
            padding: 12px 20px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
            font-size: 0.9rem;
            display: none;
        }

        .message.visible { display: block; }

        /* Pulse animation on the live dot */
        .live-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ config('vote.question') }}</h1>

        <div class="message" id="message">
            <span class="live-dot"></span> Votre vote a été enregistré ! Les résultats se mettent à jour en direct.
        </div>

        <div class="choices">
            <button class="choice-btn {{ $hasVoted && $votedFor === 'a' ? 'voted' : '' }}"
                    data-choice="a"
                    {{ $hasVoted ? 'disabled' : '' }}
                    onclick="submitVote('a')">
                <span class="check-icon">&#10003;</span>
                <div class="choice-label">{{ config('vote.option_a.label') }}</div>
                <div class="choice-desc">{{ config('vote.option_a.description') }}</div>
            </button>
            <button class="choice-btn {{ $hasVoted && $votedFor === 'b' ? 'voted' : '' }}"
                    data-choice="b"
                    {{ $hasVoted ? 'disabled' : '' }}
                    onclick="submitVote('b')">
                <span class="check-icon">&#10003;</span>
                <div class="choice-label">{{ config('vote.option_b.label') }}</div>
                <div class="choice-desc">{{ config('vote.option_b.description') }}</div>
            </button>
        </div>

        <div class="gauge-section">
            <div class="gauge-labels">
                <span class="gauge-label-a">{{ config('vote.option_a.label') }}</span>
                <span class="gauge-label-b">{{ config('vote.option_b.label') }}</span>
            </div>
            <div class="gauge-track">
                <div class="gauge-fill-a" id="fillA"></div>
                <div class="gauge-fill-b" id="fillB"></div>
            </div>
            <div class="gauge-percentages">
                <span class="pct pct-a" id="pctA">0%</span>
                <span class="pct pct-b" id="pctB">0%</span>
            </div>
        </div>

        <div class="total">
            <span class="live-dot"></span>
            <span id="totalVotes">0</span> vote(s) au total
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let hasVoted = @json($hasVoted);

        async function submitVote(choice) {
            if (hasVoted) return;

            const btns = document.querySelectorAll('.choice-btn');
            btns.forEach(b => b.disabled = true);

            try {
                const res = await fetch('/vote', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ choice }),
                });

                if (res.ok) {
                    hasVoted = true;
                    const voted = document.querySelector(`[data-choice="${choice}"]`);
                    voted.classList.add('voted');
                    document.getElementById('message').classList.add('visible');
                    fetchResults();
                }
            } catch (e) {
                btns.forEach(b => b.disabled = false);
            }
        }

        async function fetchResults() {
            try {
                const res = await fetch('/api/results');
                const data = await res.json();

                const fillA = document.getElementById('fillA');
                const fillB = document.getElementById('fillB');
                const pctA = document.getElementById('pctA');
                const pctB = document.getElementById('pctB');
                const total = document.getElementById('totalVotes');

                if (data.total === 0) {
                    fillA.style.width = '50%';
                    fillB.style.width = '50%';
                    pctA.textContent = '0%';
                    pctB.textContent = '0%';
                } else {
                    fillA.style.width = data.percent_a + '%';
                    fillB.style.width = data.percent_b + '%';
                    pctA.textContent = data.percent_a + '%';
                    pctB.textContent = data.percent_b + '%';
                }

                total.textContent = data.total;
            } catch (e) {
                // silently retry on next poll
            }
        }

        // Initial fetch + polling every 2 seconds
        fetchResults();
        setInterval(fetchResults, 2000);
    </script>
</body>
</html>
