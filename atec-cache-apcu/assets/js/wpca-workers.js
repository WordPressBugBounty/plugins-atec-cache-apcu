(() => {
  const outEl = () => document.getElementById('atec-wpca-workers-out');

  function log(line = "") {
    const el = outEl();
    if (!el) return;
    el.textContent += line + "\n";
  }

  function resetLog() {
    const el = outEl();
    if (!el) return;
    el.textContent = "";
  }

  function cfg() {
    const c = window.ATEC_WPCA_WORKERS || {};
    if (!c.restUrl || !c.nonce) throw new Error("WPCA: Missing REST config (restUrl/nonce).");
    return c;
  }

  function withCacheBuster(urlStr) {
    const u = new URL(urlStr, window.location.origin);
    u.searchParams.set("r", String(Math.random()));
    return u.toString();
  }

  async function probeOnce({ sleepMs = 0 } = {}) {
    const c = cfg();
    const url = withCacheBuster(c.restUrl);

    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': c.nonce,
      },
      body: JSON.stringify({ sleep_ms: sleepMs }),
    });

    if (!res.ok) {
      const txt = await res.text().catch(() => '');
      throw new Error(`HTTP ${res.status} ${res.statusText}: ${txt}`);
    }

    return await res.json();
  }

  async function runPhase({ name, total, concurrency, sleepMs }) {
    log(`${name}: total=${total}, concurrency=${concurrency}, sleepMs=${sleepMs}`);

    const pidCounts = new Map();
    let sharedEver = false;

    let idx = 0;
    async function worker() {
      while (idx < total) {
        const n = idx++;
        const r = await probeOnce({ sleepMs });

        const pid = String(r?.pid ?? '0');
        pidCounts.set(pid, (pidCounts.get(pid) || 0) + 1);

        if (r?.shared_apcu_signal) sharedEver = true;

        // progress every ~10%
        if ((n + 1) % Math.max(1, Math.floor(total / 10)) === 0) {
          log(`…${n + 1}/${total}`);
        }
      }
    }

    const workers = [];
    for (let i = 0; i < concurrency; i++) workers.push(worker());
    await Promise.all(workers);

    const uniquePids = [...pidCounts.keys()].filter(p => p !== '0');
    uniquePids.sort((a, b) => (pidCounts.get(b) || 0) - (pidCounts.get(a) || 0));

    return { name, total, concurrency, sleepMs, pidCounts, uniquePids, sharedEver };
  }

  function printSummary(phases) {
    // aggregate across phases
    const aggPidCounts = new Map();
    let sharedEver = false;

    for (const ph of phases) {
      sharedEver = sharedEver || ph.sharedEver;
      for (const [pid, cnt] of ph.pidCounts.entries()) {
        aggPidCounts.set(pid, (aggPidCounts.get(pid) || 0) + cnt);
      }
    }

    const uniquePids = [...aggPidCounts.keys()].filter(p => p !== '0');
    uniquePids.sort((a, b) => (aggPidCounts.get(b) || 0) - (aggPidCounts.get(a) || 0));

    log("");
    log("Summary");
    log("-------");
    log(`Unique PIDs observed: ${uniquePids.length}${uniquePids.length ? ` (${uniquePids.join(', ')})` : ''}`);
    log(`APCu shared across processes detected: ${sharedEver ? "YES" : "NO"}`);
    log("");
    log("PID distribution:");
    for (const pid of uniquePids) log(`  ${pid}: ${aggPidCounts.get(pid)} hits`);
    log("");

    // Bullet-proof verdict rules:
    // - SAFE: >=2 PIDs and sharedEver true
    // - NOT SAFE: >=2 PIDs and sharedEver false, AND we had enough attempts (>=20) and used overlap phase
    // - INCONCLUSIVE: otherwise (usually 1 PID / sticky routing)
    const totalRequests = phases.reduce((s, ph) => s + ph.total, 0);
    const hasOverlap = phases.some(ph => ph.sleepMs > 0);

    if (uniquePids.length >= 2 && sharedEver) {
      log("✅ OC is SAFE: Multiple PHP workers detected and APCu memory is shared across them.");
      log("   APCu Object Cache should be coherent across requests.");
      return { verdict: "SAFE", uniquePids: uniquePids.length, sharedEver, totalRequests };
    }

    if (uniquePids.length >= 2 && !sharedEver && totalRequests >= 20 && hasOverlap) {
      log("❌ OC is NOT SAFE: Multiple PHP workers detected but APCu memory is NOT shared across them.");
      log("   APCu Object Cache can be incoherent (different workers may serve different cached values).");
      return { verdict: "NOT_SAFE", uniquePids: uniquePids.length, sharedEver, totalRequests };
    }

    log("⚠️ Result is INCONCLUSIVE: Not enough evidence to decide.");
    log("   Reasons: only one PID observed (sticky routing) or too few samples.");
    log("   Tip: run the overlap test and/or increase total requests.");
    return { verdict: "INCONCLUSIVE", uniquePids: uniquePids.length, sharedEver, totalRequests };
  }

  async function runAuto() {
    resetLog();
    log("Running automatic workers/APCu probe…");
    log("");

    // Phase A: spread
    const phaseA = await runPhase({ name: "Phase A (spread)", total: 20, concurrency: 6, sleepMs: 0 });
    log("");

    // Phase B: overlap (helps defeat sticky + increases concurrent worker usage)
    const phaseB = await runPhase({ name: "Phase B (overlap)", total: 12, concurrency: 6, sleepMs: 200 });

    return printSummary([phaseA, phaseB]);
  }

  async function runManual(opts) {
    resetLog();
    log("Manual test");
    log("----------");
    const ph = await runPhase({ name: "Phase", ...opts });
    return printSummary([ph]);
  }

  function bind(btnId, fn) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.addEventListener('click', async () => {
      btn.disabled = true;
      try {
        await fn();
      } catch (e) {
        resetLog();
        log(String(e?.message || e));
      } finally {
        btn.disabled = false;
      }
    });
  }

  document.addEventListener('DOMContentLoaded', async () => {
    // Buttons (keep your intent)
    bind('atec-wpca-workers-quick',   () => runManual({ total: 20, concurrency: 8, sleepMs: 0 }));
    bind('atec-wpca-workers-overlap', () => runManual({ total: 20, concurrency: 8, sleepMs: 200 }));

    // Auto-run
    try {
      await runAuto();
    } catch (e) {
      resetLog();
      log(String(e?.message || e));
    }
  });
})();
