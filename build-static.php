<?php
/**
 * ========================================================
 * GURUKUL STATIC SITE GENERATOR & NETLIFY BUILD SCRIPT
 * ========================================================
 * This script fetches locally rendered PHP pages, rewrites
 * all relative navigation links to support static (.html) pages,
 * refactors the contact form to integrate with Netlify Forms,
 * and saves the outputs directly to the project root directory.
 */

// Host of the running PHP dev server
$base_url = 'http://localhost:3000';

// List of public pages to compile
$pages = [
    'index.php'   => 'index.html',
    'about.php'   => 'about.html',
    'gallery.php' => 'gallery.html',
    'news.php'    => 'news.html',
    'results.php' => 'results.html',
    'contact.php' => 'contact.html'
];

echo "=============================================\n";
echo "Starting Gurukul Static HTML Compilation...\n";
echo "Target Base: {$base_url}\n";
echo "=============================================\n\n";

foreach ($pages as $source => $target) {
    $url = "{$base_url}/{$source}";
    echo "[-] Scraping {$source} from {$url}... ";
    
    // Fetch rendered HTML from the local dev server
    $html = @file_get_contents($url);
    
    if ($html === false) {
        echo "FAILED!\n";
        echo "ERROR: Could not fetch {$url}. Please make sure your local PHP development server is active on {$base_url}.\n";
        exit(1);
    }
    
    echo "OK (" . strlen($html) . " bytes)\n";
    
    // 1. Perform custom adaptations for the Contact Form on contact.html
    if ($target === 'contact.html') {
        echo "[-] Adapting contact.html form for Netlify Form Capture...\n";
        
        // Add data-netlify="true" and name="contact" to form
        $form_target = '<form id="contact-form" novalidate autocomplete="off">';
        $form_replacement = '<form id="contact-form" name="contact" data-netlify="true" novalidate autocomplete="off">' . "\n" . '                            <input type="hidden" name="form-name" value="contact">';
        
        $html = str_replace($form_target, $form_replacement, $html);
        
        // Add name attributes to the key input fields
        $html = str_replace('id="contact-name"', 'name="name" id="contact-name"', $html);
        $html = str_replace('id="contact-email"', 'name="email" id="contact-email"', $html);
        $html = str_replace('id="contact-phone"', 'name="phone" id="contact-phone"', $html);
        $html = str_replace('id="contact-message"', 'name="message" id="contact-message"', $html);
        
        // Replace AJAX database submit logic with Netlify-compatible AJAX submit
        $ajax_target = <<<EOT
                if (formValid) {
                    // Modern AJAX submission
                    const formData = new FormData();
                    formData.append('name', fields.name.input.value);
                    formData.append('email', fields.email.input.value);
                    formData.append('phone', fields.phone.input.value);
                    formData.append('message', fields.message.input.value);
                    
                    fetch('contact.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            successOverlay.style.display = 'flex';
                        } else {
                            alert(data.message || 'CMS Database write failure. Please try again.');
                        }
                    })
                    .catch(err => {
                        alert('Connectivity failure. Please try again.');
                    });
                }
EOT;

        $ajax_replacement = <<<EOT
                if (formValid) {
                    // Netlify Forms AJAX submission
                    const params = new URLSearchParams();
                    params.append('form-name', 'contact');
                    params.append('name', fields.name.input.value);
                    params.append('email', fields.email.input.value);
                    params.append('phone', fields.phone.input.value);
                    params.append('message', fields.message.input.value);
                    
                    fetch('/', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: params.toString()
                    })
                    .then(res => {
                        if (res.ok) {
                            successOverlay.style.display = 'flex';
                        } else {
                            alert('Form submission capture failed. Please try again.');
                        }
                    })
                    .catch(err => {
                        alert('Connectivity failure. Please try again.');
                    });
                }
EOT;

        $html = str_replace($ajax_target, $ajax_replacement, $html);
    }

    // 2. Rewrite relative navigation & logo links
    $html = str_replace(
        ['index.php', 'about.php', 'gallery.php', 'news.php', 'results.php', 'contact.php'],
        ['index.html', 'about.html', 'gallery.html', 'news.html', 'results.html', 'contact.html'],
        $html
    );
    
    // Save to the root directory
    $output_path = __DIR__ . '/' . $target;
    if (file_put_contents($output_path, $html) !== false) {
        echo "[+] Saved compiled static page to {$target}\n\n";
    } else {
        echo "[!] Error saving compiled page to {$target}!\n\n";
        exit(1);
    }
}

echo "=============================================\n";
echo "Static Compilation Completed Successfully!\n";
echo "=============================================\n";
?>
