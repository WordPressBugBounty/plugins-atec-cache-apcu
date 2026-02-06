<?php
namespace ATEC_WPCA;
defined('ABSPATH') || exit;

use ATEC\TOOLS;

return function($una) 
{
	TOOLS::little_block(__('Test Results', 'atec-cache-apcu'));

	echo '<code class="atec-border-white atec-code" id="atec-wpca-workers-out" style="white-space:pre-wrap;overflow:auto;"></code>';

	//$rest_url = rest_url('atec-wpca/v1/worker-probe');
	$rest_url = add_query_arg(
	'rest_route',
	'/atec-wpca/v1/worker-probe',
	site_url('/')   // WP install base (/wordpress/)
	);

	$rest_nonce = wp_create_nonce('wp_rest');

	echo '<script>
	const LS_KEY = "atec_wpca_worker_test";

	function saveLocal(result){
	try {
		localStorage.setItem(LS_KEY, JSON.stringify(result));
	} catch(e) {}
	}

	function loadLocal(){
	try {
		const s = localStorage.getItem(LS_KEY);
		return s ? JSON.parse(s) : null;
	} catch(e) { return null; }
	}

	(function(){
	const REST_URL = ' . json_encode($rest_url) . ';
	const REST_NONCE = ' . json_encode($rest_nonce) . ';

	const out = document.getElementById("atec-wpca-workers-out");
	function reset(){ if(out) out.textContent = ""; }
	function log(s){ if(out) out.textContent += s + "\\n"; }

	async function probeOnce(sleepMs){
	const urlObj = new URL(REST_URL, window.location.origin);
	urlObj.searchParams.set("r", String(Math.random()));
	const url = urlObj.toString();

	const res = await fetch(url, {
		method: "POST",
		headers: {
		"Content-Type": "application/json",
		"X-WP-Nonce": REST_NONCE
		},
		body: JSON.stringify({ sleep_ms: sleepMs || 0 })
	});

	if(!res.ok){
		let txt = "";
		try { txt = await res.text(); } catch(e){}
		throw new Error("HTTP " + res.status + " " + res.statusText + (txt ? (": " + txt) : ""));
	}
	return await res.json();
	}

	async function runTest(total, concurrency, sleepMs){
		reset();
		log("Running test: total=" + total + ", concurrency=" + concurrency + ", sleepMs=" + sleepMs);

		const pidCounts = new Map();
		let sharedEver = false;

		let idx = 0;
		async function worker(){
			while(idx < total){
				const n = idx++;
				const r = await probeOnce(sleepMs);

				const pid = String(r && r.pid ? r.pid : 0);
				pidCounts.set(pid, (pidCounts.get(pid) || 0) + 1);

				if (r && r.shared_apcu_signal) sharedEver = true;

				if ((n+1) % Math.max(1, Math.floor(total/5)) === 0) {
					log("…" + (n+1) + "/" + total);
				}
			}
		}

		const ws = [];
		for(let i=0;i<concurrency;i++) ws.push(worker());
		await Promise.all(ws);

		const pids = Array.from(pidCounts.keys()).filter(p => p !== "0");
		pids.sort((a,b) => (pidCounts.get(b)||0) - (pidCounts.get(a)||0));

		log("");
		log("Summary");
		log("-------");
		log("Unique PIDs observed: " + pids.length + (pids.length ? (" (" + pids.join(", ") + ")") : ""));
		log("APCu shared across processes detected: " + (sharedEver ? "YES" : "NO"));
		log("");
		log("PID distribution:");
		for(const pid of pids){
			log("  " + pid + ": " + pidCounts.get(pid) + " hits");
		}
		log("");
		log("APCu Object Cache status:");
		log("-------------------------");

		if (pids.length <= 1) {
		log("⚠️ INCONCLUSIVE");
		log("Only one PHP worker was observed.");
		log("Cannot determine whether APCu is shared.");
		} else if (sharedEver) {
		log("✅ SAFE");
		log("APCu memory is shared across PHP workers.");
		log("Cached data is consistent across requests and safe to use.");
		} else {
		log("❌ NOT SAFE");
		log("Multiple PHP workers detected, but APCu is not shared.");
		log("Object Cache may return inconsistent results.");
		}

		let verdict = "unknown";
		if (pids.length <= 1) verdict = "🟡 inconclusive";
		else if (sharedEver) verdict = "🟢 safe";
		else verdict = "🔴 not_safe";

		saveLocal({
		verdict,
		uniquePids: pids.length,
		shared: !!sharedEver,
		pids: pids,
		ts: Date.now()
		});

		//log("");
		//log("Saved result locally ✅");

	}

	const btnQuick = document.getElementById("atec-wpca-workers-quick");
	const btnOverlap = document.getElementById("atec-wpca-workers-overlap");

	function disableButtons(disabled){
		btnQuick && (btnQuick.disabled = disabled);
		btnOverlap && (btnOverlap.disabled = disabled);
	}

	function bind(btn, fn){
		if(!btn) return;
		btn.addEventListener("click", async function(){
			disableButtons(true);
			try { await fn(); }
			catch(e){ reset(); log(String(e && e.message ? e.message : e)); }
			finally { disableButtons(false); }
		});
	}

	// quick: more requests, no delay
	bind(btnQuick, () => runTest(20, 8, 0));

	// overlap: fewer requests but each sleeps a bit server-side to increase chance of different workers
	bind(btnOverlap, () => runTest(20, 8, 200));

	// auto-run light test on load (keeps noise low)
	disableButtons(true);
	runTest(10, 4, 0)
		.catch(e => { reset(); log(String(e && e.message ? e.message : e)); })
		.finally(() => disableButtons(false));

	})();
	</script>';
};
?>
