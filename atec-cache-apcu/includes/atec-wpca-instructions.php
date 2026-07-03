<?php
defined('ABSPATH') || exit;

use ATEC\TOOLS;

/** Tab-keyed Instructions renderers — keys must match $una->nav. */
return [
	'Cache' => static function () {

		TOOLS::div('box',
			'<strong>Cache</strong> shows whether the WordPress runtime object cache and APCu persistent object cache are active, with per-layer flush and optional OC stats.'
		);

		TOOLS::ul('How to use', [
			'Green badges mean that layer is enabled; grey means missing or off.',
			'Use the trash button on a block to flush that cache layer only.',
			'<strong>WP</strong> clears the in-request WordPress object cache; <strong>APCu</strong> clears the persistent APCu object cache drop-in.',
			'When the object-cache drop-in is active, hit/miss stats appear below the blocks.',
		]);

		TOOLS::ul('Tips', [
			'Flush APCu after deploying PHP or plugin changes if cached data looks stale.',
			'Enable object cache in <strong>Settings</strong> before expecting APCu entries on the <strong>APCu</strong> tab.',
			'Pair with page cache (Settings) for full-stack caching — this tab covers object cache only.',
		]);

		TOOLS::div(-1);
	},

	'Settings' => static function () {

		TOOLS::div('box',
			'<strong>Settings</strong> controls APCu object cache and optional page cache — toggles, PRO modes, and the compatibility check.'
		);

		TOOLS::ul('How to use', [
			'<strong>OC Settings</strong> (left) — enable the drop-in, then Save. Green badges confirm the drop-in is active.',
			'<strong>PC Settings</strong> (right) — optional HTML cache; enable only when object cache is working.',
			'<strong>Allow APCu Compatibility Check</strong> — adds the test tab; run it once on a new server, then you can turn the option off.',
			'Use the inline <strong>help</strong> buttons at the bottom of each column for field details.',
		]);

		TOOLS::ul('PRO', ['Advanced object cache and page cache modes require a PRO license.']);

		TOOLS::ul('Tips', [
			'If settings look inconsistent after a deploy, Save again on the OC Settings form.',
			'Do not run multiple page-cache plugins — see the warning help on the PC Settings column.',
			'Header badges show OC/PC version, salts, and Zlib status.',
		]);

		TOOLS::div(-1);
	},

	'APCu' => static function () {

		TOOLS::div('box',
			'<strong>APCu</strong> lists keys stored in APCu memory: WordPress object-cache entries, other persistent items, and page-cache keys.'
		);

		TOOLS::ul('How to use', [
			'The first table shows WP object-cache groups and keys (when the drop-in is active). Use trash on a row to delete one key.',
			'<strong>Flush</strong> in the header clears all WP object-cache entries in APCu.',
			'<strong>Other persistent items</strong> lists APCu keys not managed by this plugin.',
			'<strong>Page Cache</strong> lists raw page-cache keys (violet) — use the <strong>Page Cache</strong> tab for readable page titles and links.',
		]);

		TOOLS::ul('Tips', [
			'Large hit counts and sizes help spot heavy cache groups.',
			'Serialized values are truncated in the table — delete and regenerate if a value looks corrupt.',
			'If the object-cache table is empty, enable object cache in <strong>Settings</strong> and load a few front-end pages first.',
		]);

		TOOLS::div(-1);
	},

	'Page_Cache' => static function () {

		TOOLS::div('box',
			'<strong>Page Cache</strong> lists HTML pages and feeds stored in APCu, with hits, size, title, and a link to the cached URL.'
		);

		TOOLS::ul('How to use', [
			'The header shows Zlib compression status and your page-cache salt (🧂).',
			'Use trash on a row to drop one cached page or archive.',
			'<strong>Empty page cache</strong> clears all page-cache entries at once.',
			'Icons mark RSS feeds; the page column shows pagination for category/tag archives.',
		]);

		TOOLS::ul('Tips', [
			'Enable page cache in <strong>Settings</strong> and visit a few URLs before expecting rows here.',
			'After theme or content changes, flush affected pages or empty the full page cache.',
			'If Zlib is on at the server level, leave the plugin Zlib option aligned to avoid double compression.',
		]);

		TOOLS::div(-1);
	},

	'Debug' => static function () {

		TOOLS::div('box',
			'<strong>Debug</strong> (PRO) compares the WordPress runtime object cache with APCu persistence — for troubleshooting drop-in sync and <code>alloptions</code> mismatches.'
		);

		TOOLS::ul('How to use', [
			'The settings-cache test writes a temp option, reads it back, flushes runtime cache, and verifies APCu still returns the value.',
			'The main table lists runtime cache keys vs APCu copies — green means present and equal; red means mismatch.',
			'Violet group names are non-persistent groups (expected to differ or skip APCu).',
			'Trash removes one APCu entry by key suffix.',
		]);

		TOOLS::ul('Tips', [
			'Use only on staging or when support asks — not for day-to-day operation.',
			'An <strong>alloptions</strong> mismatch section appears when WordPress options differ between runtime and APCu.',
			'If many rows are red after a deploy, flush object cache on the <strong>Cache</strong> tab and reload.',
		]);

		TOOLS::div(-1);
	},

	'APCu_Compatibility_Check' => static function () {

		TOOLS::div('box',
			'<strong>APCu Compatibility Check</strong> sends concurrent REST requests to detect how many PHP workers handle your site and whether APCu memory is shared between them.'
		);

		TOOLS::ul('How to use', [
			'A light test runs automatically when you open this tab.',
			'<strong>Quick test</strong> fires many parallel requests with no server delay — good for spotting multiple workers quickly.',
			'<strong>Overlap test</strong> adds a short server-side sleep so concurrent requests are more likely to land on different workers.',
			'Read the log below for PID counts and the final verdict.',
		]);

		TOOLS::ul('Verdicts', [
			'<strong>SAFE</strong> — multiple PHP workers were seen and APCu is shared; object cache should be coherent.',
			'<strong>NOT SAFE</strong> — multiple workers but APCu is not shared; cached values may differ per worker.',
			'<strong>INCONCLUSIVE</strong> — only one worker was observed (sticky routing) or not enough evidence; try the overlap test.',
		]);

		TOOLS::ul('Tips', [
			'The latest result is stored in your browser and shown on the <strong>Settings</strong> tab under OC status.',
			'Enable <strong>REST worker probe</strong> in Settings if this tab or the tests fail with a missing REST config error.',
			'On busy hosts, run the overlap test twice if the verdict stays inconclusive.',
		]);

		TOOLS::div(-1);
	},
];
