<x-layouts.studio :components="$components" :current="$componentId">
    <div
        x-data="componentStudio()"
        style="display:flex;flex-direction:column;height:100%;overflow:hidden;"
    >
        {{-- Preview Area --}}
        <div style="flex:1;min-height:180px;position:relative;overflow:hidden;background:repeating-conic-gradient(#f9fafb 0% 25%, transparent 0% 50%) 50% / 20px 20px; background:#ffffff">
            {{-- Drag overlay — blocks iframe from capturing mouse events during resize --}}
            <div x-show="dragging" style="position:absolute;inset:0;z-index:10;cursor:row-resize;" x-cloak></div>

            {{-- Live Preview (iframe with srcdoc) --}}
            <iframe
                x-ref="previewFrame"
                :srcdoc="previewHtml"
                style="width:100%;height:100%;border:none;display:block;"
            ></iframe>
        </div>

        {{-- Resizer Handle --}}
        <div
            @mousedown="startResize($event)"
            style="height:6px;background:#e5e7eb;cursor:row-resize;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background 0.15s;user-select:none;"
            onmouseover="this.style.background='#d1d5db'" onmouseout="this.style.background='#e5e7eb'"
        >
            <svg width="20" height="4" viewBox="0 0 20 4" fill="#9ca3af">
                <circle cx="4" cy="2" r="1.5"/><circle cx="10" cy="2" r="1.5"/><circle cx="16" cy="2" r="1.5"/>
            </svg>
        </div>

        {{-- Controls Area --}}
        <div style="height:300px;min-height:120px;display:flex;flex-direction:column;overflow:hidden;background:#fff;" x-ref="controlsPanel">
            {{-- Tabs --}}
            <div style="display:flex;align-items:center;gap:4px;padding:0px;border-bottom:1px solid #e5e7eb;flex-shrink:0;background:#fafafa;">
                <template x-for="t in ['props', 'slots', 'code']" :key="t">
                    <button
                        @click="tab = t"
                        :style="tab === t
                            ? 'color:#2563eb;font-weight:600;border-bottom:2px solid #2563eb; padding:8px 14px; font-size:13px; cursor:pointer; text-transform: capitalize'
                            : 'color:#6b7280;font-weight:400;border-bottom:2px solid transparent; padding:8px 14px; font-size:13px; cursor:pointer; text-transform: capitalize'"
                        style="padding:8px 14px;font-size:13px;background:none;border:none;border-top:none;border-left:none;border-right:none;cursor:pointer;transition:color 0.15s;text-transform:capitalize;margin-bottom:-9px;"
                        x-text="t"
                    ></button>
                </template>
            </div>

            {{-- Tab Content --}}
            <div style="flex:1;overflow-y:auto;">
                {{-- Props Tab --}}
                <div x-show="tab === 'props'" style="padding:0;">
                    @if(count($props) > 0)
                        <div style="display:grid;grid-template-columns:150px 1fr 110px 220px;padding:8px 16px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;position:sticky;top:0;z-index:1;">
                            <div>Name</div>
                            <div>Description</div>
                            <div>Default</div>
                            <div>Controls</div>
                        </div>
                        @foreach($props as $i => $prop)
                            <div style="display:grid;grid-template-columns:150px 1fr 110px 220px;padding:10px 16px;border-bottom:1px solid #f3f4f6;align-items:center;font-size:13px;">
                                <div>
                                    <code style="font-size:12px;color:#7c3aed;background:#f5f3ff;padding:2px 6px;border-radius:4px;font-family:'SF Mono',SFMono-Regular,Consolas,monospace;">{{ $prop['name'] }}</code>
                                </div>
                                <div style="color:#6b7280;font-size:12px;padding-right:12px;">{{ $prop['description'] ?? '' }}</div>
                                <div>
                                    @php $default = $prop['default'] ?? null; @endphp
                                    <code style="font-size:11px;color:#9ca3af;font-family:'SF Mono',SFMono-Regular,Consolas,monospace;">{{ is_bool($default) ? ($default ? 'true' : 'false') : ($default ?? '—') }}</code>
                                </div>
                                <div>
                                    @if(($prop['type'] ?? 'text') === 'select')
                                        <select
                                            x-model="attrs['{{ $prop['name'] }}']"
                                            @change="generateCode()"
                                            style="width:100%;padding:5px 8px;font-size:12px;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#374151;outline:none;"
                                        >
                                            @foreach(($prop['options'] ?? []) as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @elseif(($prop['type'] ?? 'text') === 'boolean')
                                        @php $boolCheck = "attrs['" . $prop['name'] . "'] === 'true' || attrs['" . $prop['name'] . "'] === true"; @endphp
                                        <div
                                            role="switch"
                                            x-bind:aria-checked="{{ $boolCheck }}"
                                            x-on:click="attrs['{{ $prop['name'] }}'] = ({{ $boolCheck }}) ? 'false' : 'true'; generateCode()"
                                            x-bind:style="({{ $boolCheck }}) ? 'background-color:#18181b' : 'background-color:#e4e4e7'"
                                            style="position:relative;display:inline-flex;height:24px;width:44px;flex-shrink:0;cursor:pointer;align-items:center;border-radius:9999px;border:none;padding:2px;box-sizing:border-box;background-color:#e4e4e7;transition:background-color 0.15s cubic-bezier(0.4,0,0.2,1);"
                                        >
                                            <span
                                                x-bind:style="({{ $boolCheck }}) ? 'transform:translateX(20px)' : 'transform:translateX(0px)'"
                                                style="pointer-events:none;display:block;height:20px;width:20px;border-radius:9999px;background-color:#fff;box-shadow:0 1px 3px 0 rgba(0,0,0,0.1),0 1px 2px -1px rgba(0,0,0,0.1);transition:transform 0.15s cubic-bezier(0.4,0,0.2,1);"
                                            ></span>
                                        </div>
                                    @elseif(($prop['type'] ?? 'text') === 'textarea')
                                        <textarea
                                            x-model="attrs['{{ $prop['name'] }}']"
                                            @input.debounce.300ms="generateCode()"
                                            rows="2"
                                            style="width:100%;padding:5px 8px;font-size:12px;border:1px solid #d1d5db;border-radius:6px;resize:vertical;font-family:inherit;color:#374151;outline:none;"
                                        ></textarea>
                                    @elseif(($prop['type'] ?? 'text') === 'integer' || ($prop['type'] ?? 'text') === 'number')
                                        <input
                                            type="number"
                                            x-model="attrs['{{ $prop['name'] }}']"
                                            @input.debounce.300ms="generateCode()"
                                            style="width:100%;padding:5px 8px;font-size:12px;border:1px solid #d1d5db;border-radius:6px;color:#374151;outline:none;"
                                        />
                                    @else
                                        <input
                                            type="text"
                                            x-model="attrs['{{ $prop['name'] }}']"
                                            @input.debounce.300ms="generateCode()"
                                            style="width:100%;padding:5px 8px;font-size:12px;border:1px solid #d1d5db;border-radius:6px;color:#374151;outline:none;"
                                            placeholder="{{ $prop['default'] ?? '' }}"
                                        />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div style="padding:2.5rem;text-align:center;color:#9ca3af;font-size:14px;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 0.75rem;opacity:0.4;">
                                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                            </svg>
                            This component has no configurable props.
                        </div>
                    @endif
                </div>

                {{-- Slots Tab --}}
                <div x-show="tab === 'slots'" style="padding:0;">
                    <div style="display:grid;grid-template-columns:150px 1fr 1fr;padding:8px 16px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;position:sticky;top:0;z-index:1;">
                        <div>Slot Name</div>
                        <div>Description</div>
                        <div>Content</div>
                    </div>

                    {{-- Default slot --}}
                    <div style="display:grid;grid-template-columns:150px 1fr 1fr;padding:10px 16px;border-bottom:1px solid #f3f4f6;align-items:start;font-size:13px;">
                        <div>
                            <code style="font-size:12px;color:#059669;background:#ecfdf5;padding:2px 6px;border-radius:4px;font-family:'SF Mono',SFMono-Regular,Consolas,monospace;">slot</code>
                            <span style="font-size:10px;color:#9ca3af;margin-left:4px;">(default)</span>
                        </div>
                        <div style="color:#6b7280;font-size:12px;">The default slot content</div>
                        <div>
                            <textarea
                                x-model="slotValues['slot']"
                                @input.debounce.300ms="generateCode()"
                                rows="2"
                                style="width:100%;padding:6px 8px;font-size:12px;border:1px solid #d1d5db;border-radius:6px;resize:vertical;font-family:inherit;color:#374151;outline:none;"
                            ></textarea>
                        </div>
                    </div>

                    @foreach($slots as $slot)
                        @if($slot['name'] !== 'slot')
                            <div style="display:grid;grid-template-columns:150px 1fr 1fr;padding:10px 16px;border-bottom:1px solid #f3f4f6;align-items:start;font-size:13px;">
                                <div>
                                    <code style="font-size:12px;color:#059669;background:#ecfdf5;padding:2px 6px;border-radius:4px;font-family:'SF Mono',SFMono-Regular,Consolas,monospace;">{{ $slot['name'] }}</code>
                                </div>
                                <div style="color:#6b7280;font-size:12px;">{{ $slot['description'] ?? '' }}</div>
                                <div>
                                    <textarea
                                        x-model="slotValues['{{ $slot['name'] }}']"
                                        @input.debounce.300ms="generateCode()"
                                        rows="2"
                                        style="width:100%;padding:6px 8px;font-size:12px;border:1px solid #d1d5db;border-radius:6px;resize:vertical;font-family:inherit;color:#374151;outline:none;"
                                    ></textarea>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Code Tab --}}
                <div x-show="tab === 'code'" style="padding:0;position:relative;">
                    <div style="position:relative;">
                        {{-- Action buttons --}}
                        <div style="position:absolute;top:10px;right:12px;display:flex;gap:6px;z-index:5;">
                            <button
                                @click="editMode = !editMode"
                                :style="editMode ? 'background:rgba(37,99,235,0.3);border-color:rgba(37,99,235,0.4);' : 'background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.15);'"
                                style="padding:6px 12px;font-size:11px;font-weight:500;border:1px solid;border-radius:6px;color:#cdd6f4;cursor:pointer;display:flex;align-items:center;gap:4px;transition:all 0.15s;backdrop-filter:blur(4px);"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                <span x-text="editMode ? 'Editing' : 'Edit'"></span>
                            </button>
                            <button
                                x-show="editMode"
                                @click="renderCustomCode()"
                                style="padding:6px 12px;font-size:11px;font-weight:500;border:1px solid rgba(52,211,153,0.3);border-radius:6px;background:rgba(52,211,153,0.2);color:#34d399;cursor:pointer;display:flex;align-items:center;gap:4px;transition:all 0.15s;backdrop-filter:blur(4px);"
                                x-cloak
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                                Render
                            </button>
                            <button
                                @click="copyCode()"
                                style="padding:6px 12px;font-size:11px;font-weight:500;border:1px solid rgba(255,255,255,0.15);border-radius:6px;background:rgba(255,255,255,0.1);color:#cdd6f4;cursor:pointer;display:flex;align-items:center;gap:5px;transition:all 0.15s;backdrop-filter:blur(4px);"
                            >
                                <svg x-show="!copied" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                </svg>
                                <svg x-show="copied" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-cloak>
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </div>

                        {{-- Read-only code display --}}
                        <pre x-show="!editMode" style="margin:0;padding:1.5rem;padding-right:14rem;background:#1e1e2e;color:#cdd6f4;font-size:13px;line-height:1.7;overflow:auto;font-family:'SF Mono',SFMono-Regular,Consolas,'Liberation Mono',Menlo,monospace;min-height:120px;"><code x-text="code"></code></pre>

                        {{-- Editable code textarea --}}
                        <div x-show="editMode" x-cloak>
                            <textarea
                                x-model="code"
                                style="width:100%;min-height:140px;padding:1.5rem;padding-right:14rem;background:#1e1e2e;color:#cdd6f4;font-size:13px;line-height:1.7;font-family:'SF Mono',SFMono-Regular,Consolas,'Liberation Mono',Menlo,monospace;border:none;outline:none;resize:vertical;"
                                spellcheck="false"
                            ></textarea>
                            <div style="padding:8px 16px;background:#181825;border-top:1px solid #313244;display:flex;align-items:center;gap:8px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f9e2af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                <span style="font-size:11px;color:#a6adc8;">Edit the Blade code above, then click <strong style="color:#34d399;">Render</strong> to see the result. You can use any Blade syntax including raw PHP.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function componentStudio() {
            return {
                tab: 'props',
                copied: false,
                editMode: false,
                dragging: false,
                code: @json($code),
                component: @json($componentId),
                container: @json($container),
                attrs: @json($attrs),
                slotValues: @json($slotValues),
                previewHtml: @json($previewDoc),

                _refreshTimer: null,

                init() {
                    window.addEventListener('message', (e) => {
                        if (e.data && e.data.type === 'preview-html') {
                            this.previewHtml = e.data.html;
                        }
                    });
                    // Rewrite http://localhost asset URLs to relative paths and inject
                    // <base href> so they resolve against the real dev server origin
                    if (window.__studioOrigin && this.previewHtml) {
                        this.previewHtml = this.previewHtml.replace(/https?:\/\/localhost(\/[^"'\s]+)/g, '$1');
                        var baseTag = '<base href="' + window.__studioOrigin + '/">';
                        if (this.previewHtml.indexOf('<head>') !== -1) {
                            this.previewHtml = this.previewHtml.replace('<head>', '<head>' + baseTag);
                        }
                    }
                },

                generateCode() {
                    // Build Blade component tag strings
                    // Note: We split '<'+'x-' to prevent Blade's compiler from
                    // parsing these JavaScript strings as actual Blade directives
                    var xOpen = '<' + 'x-';
                    var xSlotOpen = '<' + 'x-slot:';
                    var xSlotClose = '</' + 'x-slot:';
                    var xClose = '</' + 'x-';

                    let tagName = 'katana.' + this.component;
                    let lines = [xOpen + tagName];
                    let attrLines = [];

                    for (let key in this.attrs) {
                        let value = this.attrs[key];
                        if (value === '' || value === null || value === undefined) continue;
                        if (value === false || value === 'false') continue;

                        if (value === true || value === 'true' || value === '1') {
                            attrLines.push('    ' + key);
                        } else if (Array.isArray(value) || (typeof value === 'object' && value !== null)) {
                            attrLines.push('    :' + key + '="' + this._toPhpArray(value) + '"');
                        } else if (typeof value === 'string' && (value.charAt(0) === '[' || value.charAt(0) === '{')) {
                            try {
                                let parsed = JSON.parse(value);
                                attrLines.push('    :' + key + '="' + this._toPhpArray(parsed) + '"');
                            } catch(e) {
                                attrLines.push('    ' + key + '="' + value.replace(/"/g, '&quot;') + '"');
                            }
                        } else {
                            attrLines.push('    ' + key + '="' + String(value).replace(/"/g, '&quot;') + '"');
                        }
                    }

                    if (attrLines.length > 0) {
                        lines[0] += '\n' + attrLines.join('\n');
                    }

                    let defaultSlot = (this.slotValues['slot'] || '').trim();
                    let hasNamedSlots = false;
                    let namedSlotLines = [];

                    for (let name in this.slotValues) {
                        if (name === 'slot') continue;
                        let content = (this.slotValues[name] || '').trim();
                        if (content) {
                            hasNamedSlots = true;
                            namedSlotLines.push('    ' + xSlotOpen + name + '>' + content + xSlotClose + name + '>');
                        }
                    }

                    if (!defaultSlot && !hasNamedSlots) {
                        lines.push('/>');
                    } else {
                        lines.push('>');
                        if (defaultSlot) lines.push('    ' + defaultSlot);
                        namedSlotLines.forEach(function(l) { lines.push(l); });
                        lines.push(xClose + tagName + '>');
                    }

                    this.code = lines.join('\n');
                    this.scheduleRefresh();
                },

                scheduleRefresh() {
                    clearTimeout(this._refreshTimer);
                    this._refreshTimer = setTimeout(() => { this.refreshPreview(); }, 400);
                },

                buildQueryParams() {
                    let params = new URLSearchParams();
                    for (let key in this.attrs) {
                        if (this.attrs[key] !== '' && this.attrs[key] !== null && this.attrs[key] !== undefined) {
                            params.set('attrs[' + key + ']', this.attrs[key]);
                        }
                    }
                    for (let key in this.slotValues) {
                        if (this.slotValues[key] !== '' && this.slotValues[key] !== null && this.slotValues[key] !== undefined) {
                            params.set('slots[' + key + ']', this.slotValues[key]);
                        }
                    }
                    return params.toString();
                },

                buildPreviewUrl() {
                    return '/preview/' + this.component + '?' + this.buildQueryParams();
                },

                buildComponentUrl() {
                    return '/component/' + this.component + '?' + this.buildQueryParams();
                },

                refreshPreview() {
                    clearTimeout(this._refreshTimer);
                    let path = this.buildPreviewUrl();
                    window.parent.postMessage({ type: 'wasm-render-preview', path: path }, '*');
                    // Keep browser URL in sync with current prop/slot state
                    history.replaceState(null, '', this.buildComponentUrl());
                },

                renderCustomCode() {
                    let path = '/render?code=' + encodeURIComponent(this.code);
                    window.parent.postMessage({ type: 'wasm-render-preview', path: path }, '*');
                },

                copyCode() {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(this.code).catch(function() {});
                    }
                    // Fallback for sandboxed iframes
                    try {
                        let ta = document.createElement('textarea');
                        ta.value = this.code;
                        ta.style.position = 'fixed';
                        ta.style.opacity = '0';
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        ta.remove();
                    } catch(e) {}
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2000);
                },

                _toPhpArray(val) {
                    if (Array.isArray(val)) {
                        return '[' + val.map(v => this._toPhpArray(v)).join(', ') + ']';
                    } else if (val !== null && typeof val === 'object') {
                        let items = Object.entries(val).map(([k, v]) => "'" + k.replace(/'/g, "\\'") + "' => " + this._toPhpArray(v));
                        return '[' + items.join(', ') + ']';
                    } else if (typeof val === 'string') {
                        return "'" + val.replace(/'/g, "\\'") + "'";
                    } else if (typeof val === 'boolean') {
                        return val ? 'true' : 'false';
                    } else if (val === null) {
                        return 'null';
                    }
                    return String(val);
                },

                startResize(e) {
                    let self = this;
                    let panel = this.$refs.controlsPanel;
                    let startY = e.clientY;
                    let startHeight = panel.offsetHeight;
                    self.dragging = true;

                    let onMove = function(ev) {
                        let diff = startY - ev.clientY;
                        let newHeight = Math.max(120, Math.min(600, startHeight + diff));
                        panel.style.height = newHeight + 'px';
                    };
                    let onUp = function() {
                        self.dragging = false;
                        document.removeEventListener('mousemove', onMove);
                        document.removeEventListener('mouseup', onUp);
                    };

                    document.addEventListener('mousemove', onMove);
                    document.addEventListener('mouseup', onUp);
                }
            };
        }
    </script>
</x-layouts.studio>
