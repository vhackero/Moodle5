<?php
// This file is part of Moodle - http://moodle.org/

namespace local_segmentmenu;

defined('MOODLE_INTERNAL') || die();

class hook_callbacks {
    public static function before_standard_top_of_body(\core\hook\output\before_standard_top_of_body_html_generation $hook): void {
        global $USER;

        if (!isloggedin() || isguestuser() || during_initial_install()) {
            return;
        }

        if (AJAX_SCRIPT || CLI_SCRIPT || WS_SERVER) {
            return;
        }

        $items = item_repository::get_menu_items_for_user(
            segment_resolver::get_current_user_segment(),
            segment_resolver::get_current_user_course_roles(),
            segment_resolver::get_current_courseid()
        );
        if (!$items) {
            return;
        }

        $heading = s(get_string('menuheading', 'local_segmentmenu'));
        $resetlabel = s(get_string('resetposition', 'local_segmentmenu'));
        $draglabel = s(get_string('dragmenu', 'local_segmentmenu'));
        $position = get_config('local_segmentmenu', 'menuposition') ?: 'right';
        if (!in_array($position, ['right', 'left', 'sticky'], true)) {
            $position = 'right';
        }
        $storagekey = s('local_segmentmenu_position_' . $USER->id);

        $links = '';
        foreach ($items as $item) {
            try {
                $url = new \moodle_url($item->url);
            } catch (\Throwable $e) {
                continue;
            }

            $attributes = ['class' => 'local-segmentmenu__link'];
            if (($item->linktarget ?? 'same') === 'new') {
                $attributes['target'] = '_blank';
                $attributes['rel'] = 'noopener noreferrer';
            }

            $links .= \html_writer::link($url, s($item->name), $attributes);
        }

        if ($links === '') {
            return;
        }

        $html = <<<HTML
<style>
.local-segmentmenu {
    position: fixed;
    top: 64px;
    right: 18px;
    z-index: 1050;
    font-family: inherit;
    color: #1d2125;
}
.local-segmentmenu--left {
    right: auto;
    left: 18px;
}
.local-segmentmenu--sticky {
    position: sticky;
    top: 0;
    right: auto;
    left: auto;
    z-index: 1040;
    width: 100%;
    background: #fff;
    border-bottom: 1px solid #d8dee5;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
}
.local-segmentmenu details {
    min-width: 280px;
    max-width: min(380px, calc(100vw - 36px));
    background: #ffffff;
    border: 1px solid #d9e2ec;
    border-radius: 8px;
    box-shadow: 0 14px 38px rgba(20, 33, 61, .18);
    overflow: hidden;
}
.local-segmentmenu--sticky details {
    min-width: 0;
    max-width: 1180px;
    margin: 0 auto;
    border: 0;
    border-radius: 0;
    box-shadow: none;
}
.local-segmentmenu summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    cursor: pointer;
    list-style: none;
    padding: 11px 12px 11px 14px;
    font-weight: 600;
    color: #102a43;
    background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
    border-bottom: 1px solid transparent;
    user-select: none;
}
.local-segmentmenu summary::-webkit-details-marker {
    display: none;
}
.local-segmentmenu summary::after {
    content: "";
    width: 8px;
    height: 8px;
    border-bottom: 2px solid #486581;
    border-right: 2px solid #486581;
    transform: rotate(45deg);
    transition: transform .16s ease;
    flex: 0 0 auto;
}
.local-segmentmenu details[open] summary {
    border-bottom-color: #d9e2ec;
}
.local-segmentmenu details[open] summary::after {
    transform: rotate(225deg);
}
.local-segmentmenu__title {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}
.local-segmentmenu__grip {
    display: inline-flex;
    color: #627d98;
    cursor: grab;
}
.local-segmentmenu__grip svg,
.local-segmentmenu__reset svg {
    display: block;
}
.local-segmentmenu__tools {
    display: flex;
    justify-content: flex-end;
    padding: 7px 8px 0;
}
.local-segmentmenu__reset {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: 1px solid transparent;
    border-radius: 6px;
    background: transparent;
    color: #486581;
    cursor: pointer;
    font: inherit;
    padding: 0;
}
.local-segmentmenu__reset:hover,
.local-segmentmenu__reset:focus {
    background: #eef6ff;
    border-color: #bcccdc;
    color: #0f6cbf;
    outline: none;
}
.local-segmentmenu__links {
    display: flex;
    flex-direction: column;
    max-height: 60vh;
    overflow-y: auto;
    padding: 6px 8px 10px;
    gap: 2px;
}
.local-segmentmenu--moved {
    position: fixed;
    right: auto;
    left: auto;
    width: auto;
}
.local-segmentmenu--moved details {
    min-width: 280px;
    max-width: min(380px, calc(100vw - 36px));
    border: 1px solid #d9e2ec;
    border-radius: 8px;
    box-shadow: 0 14px 38px rgba(20, 33, 61, .18);
}
.local-segmentmenu--sticky .local-segmentmenu__links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    max-height: 50vh;
}
.local-segmentmenu__link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 10px;
    border-radius: 6px;
    color: #0f6cbf;
    text-decoration: none;
    line-height: 1.3;
    transition: background-color .12s ease, color .12s ease, transform .12s ease;
}
.local-segmentmenu__link::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: #62b0e8;
    flex: 0 0 auto;
}
.local-segmentmenu__link:hover,
.local-segmentmenu__link:focus {
    background: #eef5fc;
    color: #084b8a;
    text-decoration: none;
    transform: translateX(2px);
    outline: none;
}
@media (max-width: 767px) {
    .local-segmentmenu {
        top: auto;
        right: 10px;
        bottom: 12px;
        left: 10px;
    }
    .local-segmentmenu--sticky {
        top: 0;
        right: auto;
        bottom: auto;
        left: auto;
    }
    .local-segmentmenu details {
        width: 100%;
        max-width: none;
    }
}
</style>
<nav class="local-segmentmenu local-segmentmenu--{$position}" aria-label="{$heading}">
    <details>
        <summary>
            <span class="local-segmentmenu__title">
                <span class="local-segmentmenu__grip" aria-label="{$draglabel}" title="{$draglabel}">
                    <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <circle cx="9" cy="5" r="1.7" fill="currentColor"/>
                        <circle cx="15" cy="5" r="1.7" fill="currentColor"/>
                        <circle cx="9" cy="12" r="1.7" fill="currentColor"/>
                        <circle cx="15" cy="12" r="1.7" fill="currentColor"/>
                        <circle cx="9" cy="19" r="1.7" fill="currentColor"/>
                        <circle cx="15" cy="19" r="1.7" fill="currentColor"/>
                    </svg>
                </span>
                <span>{$heading}</span>
            </span>
        </summary>
        <div class="local-segmentmenu__tools">
            <button class="local-segmentmenu__reset" type="button" aria-label="{$resetlabel}" title="{$resetlabel}">
                <svg width="17" height="17" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M4 4v6h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20 12a8 8 0 0 1-14.9 4M4.6 10A8 8 0 0 1 19 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
        <div class="local-segmentmenu__links">{$links}</div>
    </details>
</nav>
<script>
(function() {
    var menu = document.querySelector('.local-segmentmenu');
    if (!menu) {
        return;
    }

    var handle = menu.querySelector('summary');
    var reset = menu.querySelector('.local-segmentmenu__reset');
    var storageKey = '{$storagekey}';
    var startX = 0;
    var startY = 0;
    var originLeft = 0;
    var originTop = 0;
    var moved = false;
    var ignoreNextClick = false;

    function clampPosition(left, top) {
        var rect = menu.getBoundingClientRect();
        return {
            left: Math.max(8, Math.min(left, window.innerWidth - rect.width - 8)),
            top: Math.max(8, Math.min(top, window.innerHeight - rect.height - 8))
        };
    }

    function setPosition(left, top, persist) {
        var position = clampPosition(left, top);
        menu.classList.add('local-segmentmenu--moved');
        menu.style.left = position.left + 'px';
        menu.style.top = position.top + 'px';
        menu.style.right = 'auto';
        menu.style.bottom = 'auto';
        if (persist) {
            try {
                window.localStorage.setItem(storageKey, JSON.stringify(position));
            } catch (e) {
                return;
            }
        }
    }

    try {
        var saved = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
        if (saved && typeof saved.left === 'number' && typeof saved.top === 'number') {
            setPosition(saved.left, saved.top, false);
        }
    } catch (e) {
        window.localStorage.removeItem(storageKey);
    }

    handle.addEventListener('pointerdown', function(event) {
        if (event.button !== 0) {
            return;
        }

        var rect = menu.getBoundingClientRect();
        startX = event.clientX;
        startY = event.clientY;
        originLeft = rect.left;
        originTop = rect.top;
        moved = false;
        handle.setPointerCapture(event.pointerId);
    });

    handle.addEventListener('pointermove', function(event) {
        if (!handle.hasPointerCapture(event.pointerId)) {
            return;
        }

        var dx = event.clientX - startX;
        var dy = event.clientY - startY;
        if (Math.abs(dx) + Math.abs(dy) > 4) {
            moved = true;
        }
        if (moved) {
            event.preventDefault();
            setPosition(originLeft + dx, originTop + dy, true);
        }
    });

    handle.addEventListener('pointerup', function(event) {
        if (handle.hasPointerCapture(event.pointerId)) {
            handle.releasePointerCapture(event.pointerId);
        }
        if (moved) {
            event.preventDefault();
            ignoreNextClick = true;
        }
    });

    handle.addEventListener('click', function(event) {
        if (ignoreNextClick) {
            event.preventDefault();
            ignoreNextClick = false;
        }
    });

    window.addEventListener('resize', function() {
        if (menu.classList.contains('local-segmentmenu--moved')) {
            var rect = menu.getBoundingClientRect();
            setPosition(rect.left, rect.top, true);
        }
    });

    reset.addEventListener('click', function(event) {
        event.preventDefault();
        try {
            window.localStorage.removeItem(storageKey);
        } catch (e) {
        }
        menu.classList.remove('local-segmentmenu--moved');
        menu.style.left = '';
        menu.style.top = '';
        menu.style.right = '';
        menu.style.bottom = '';
    });
}());
</script>
HTML;
        $hook->add_html($html);
    }
}
