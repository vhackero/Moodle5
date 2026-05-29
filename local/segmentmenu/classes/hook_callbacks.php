<?php
// This file is part of Moodle - http://moodle.org/

namespace local_segmentmenu;

defined('MOODLE_INTERNAL') || die();

class hook_callbacks {
    public static function before_standard_top_of_body(\core\hook\output\before_standard_top_of_body_html_generation $hook): void {
        global $PAGE;

        if (!isloggedin() || isguestuser() || during_initial_install()) {
            return;
        }

        if (AJAX_SCRIPT || CLI_SCRIPT || WS_SERVER) {
            return;
        }

        $items = item_repository::get_for_segment(segment_resolver::get_current_user_segment());
        if (!$items) {
            return;
        }

        $heading = s(get_string('menuheading', 'local_segmentmenu'));
        $links = '';
        foreach ($items as $item) {
            $url = new \moodle_url($item->url);
            $links .= \html_writer::link($url, s($item->name), [
                'class' => 'local-segmentmenu__link',
            ]);
        }

        $html = <<<HTML
<style>
.local-segmentmenu {
    position: fixed;
    top: 64px;
    right: 18px;
    z-index: 1050;
    font-family: inherit;
}
.local-segmentmenu details {
    min-width: 260px;
    max-width: min(360px, calc(100vw - 36px));
    background: #fff;
    border: 1px solid #cfd4da;
    border-radius: 6px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .16);
}
.local-segmentmenu summary {
    cursor: pointer;
    list-style: none;
    padding: 10px 14px;
    font-weight: 600;
    color: #1d2125;
}
.local-segmentmenu summary::-webkit-details-marker {
    display: none;
}
.local-segmentmenu summary::after {
    content: "▾";
    float: right;
    margin-left: 12px;
}
.local-segmentmenu details[open] summary {
    border-bottom: 1px solid #e9ecef;
}
.local-segmentmenu__links {
    display: flex;
    flex-direction: column;
    max-height: 60vh;
    overflow-y: auto;
    padding: 6px;
}
.local-segmentmenu__link {
    display: block;
    padding: 9px 10px;
    border-radius: 4px;
    color: #0f6cbf;
    text-decoration: none;
}
.local-segmentmenu__link:hover,
.local-segmentmenu__link:focus {
    background: #eef5fc;
    color: #084b8a;
    text-decoration: none;
}
@media (max-width: 767px) {
    .local-segmentmenu {
        top: auto;
        right: 10px;
        bottom: 12px;
        left: 10px;
    }
    .local-segmentmenu details {
        width: 100%;
        max-width: none;
    }
}
</style>
<nav class="local-segmentmenu" aria-label="{$heading}">
    <details>
        <summary>{$heading}</summary>
        <div class="local-segmentmenu__links">{$links}</div>
    </details>
</nav>
HTML;
        $hook->add_html($html);
    }
}
