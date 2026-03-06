================================================================================
                          CLAUDE LAB CONTEXT & INSTRUCTION
                          AetherVision AI – Quantum Image Analyzer v2.3
================================================================================

You are now working inside a deliberately vulnerable PHP file-upload challenge 
called "AetherVision AI – Quantum Image Analyzer v2.3".

This lab is part of a larger set of web security practice exercises. 
The story flavour text / company name / product name / version number / UI styling
can be freely changed or modernized when you rewrite explanations or create variants — 
**but the core vulnerable logic and exploitation flow must NEVER be altered.**

───────────────────────────────
     ABSOLUTELY PRESERVE THIS LOGIC
───────────────────────────────

Core logic that MUST remain unchanged in any explanation, rewrite, variant, or teaching material:

1. The server reads **only the first 256 bytes** of the uploaded file 
   and checks whether getimagesizefromstring() recognises it as IMAGETYPE_JPEG.

2. If the first 256 bytes pass as JPEG → file is accepted and moved to /uploads/

3. The **entire uploaded file** is then included via:
      @include $upload_path;

   inside an output buffer:
      ob_start();
      @include $upload_path;
      $raw_output = ob_get_clean();

4. Before the include, a heuristic tries to detect "PHP intent":
   - Looks for '<?php' or '<?=' in the first 4096 bytes (case-insensitive stripos)
   - Also tries token_get_all() looking for T_OPEN_TAG

5. Branch logic:
   if ($is_php_intent) {
       // show whatever the included file produced (attacker-controlled output)
       $output = $raw_output;
   } else {
       // show fake "quantum scan report" that mentions the FLAG constant
       // but never actually reveals its value
   }

6. The flag is defined as a constant very early:
      define('FLAG', 'flag{php_include_is_dangerous_2026_AetherBreach_polyglot}');

───────────────────────────────
          HACKING FLOW (MANDATORY EXPLANATION PATTERN)
───────────────────────────────

The intended solution path students must discover is:

Step 1. Create a polyglot file that:
   • Starts with enough valid JPEG bytes (≥ ~100–260 bytes) to pass getimagesizefromstring()
   • Somewhere later contains PHP code

Step 2. The PHP code must:
   • Run after the JPEG header has already been sent to the output buffer
   • Actively clean the garbage that was buffered (the binary JPEG bytes)
   • Output only the desired content (usually the flag)
   • Preferably terminate execution early

Most common & clean payload that reliably wins:

<?php
    ob_clean();           // ← removes the JPEG header garbage from buffer
    echo FLAG;            // or constant('FLAG') or $GLOBALS['FLAG']
    exit;                 // prevents any trailing bytes from being sent
?>

Step 3. Because detection sees '<?php' inside first 4096 bytes → $is_php_intent = true
       → server shows $raw_output instead of fake report

Step 4. Because ob_clean() was called → $raw_output contains only what the payload echoed

Result: clean flag on screen

───────────────────────────────
      RED LINES – THINGS YOU MUST NOT DO
───────────────────────────────

• Do NOT remove / weaken / comment-out / bypass the first-256-bytes JPEG check
• Do NOT remove the @include of the uploaded file
• Do NOT remove the ob_start() / ob_get_clean() wrapping
• Do NOT remove or change the $is_php_intent branch logic
• Do NOT make the detection perfect / bulletproof (it must stay naive)
• Do NOT move the FLAG constant inside a place that cannot be accessed
• Do NOT replace @include with something safe like file_get_contents + highlight_string

You may:
• Change filenames, paths, styling, version numbers, company backstory
• Add more fake "AI" / "quantum" flavour text
• Make the UI prettier or more modern
• Add extra misleading hints in comments or UI
• Create easier/harder variants (different byte counts, worse detection, etc.)

But the five core elements above (256-byte check, full-file @include, ob_start, intent heuristic, branch) must stay intact.

Now you understand the lab. 
Proceed with solving, explaining, writing variants, or teaching — while keeping this exact vulnerability structure.

Ready? What would you like to do with this challenge?
================================================================================