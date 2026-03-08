<x-layouts.studio :components="$components">
    <div style="flex:1;display:flex;align-items:center;justify-content:center;padding:3rem;">
        <div style="max-width:720px;width:100%;">
            <h2 style="font-size:2rem;font-weight:700;color:#111827;margin:0 0 0.75rem;">Welcome to Component Studio</h2>
            <p style="font-size:1.05rem;color:#6b7280;line-height:1.7;margin:0 0 0.5rem;">
                You can think of component studio like <strong style="color:#111827;">Storybook</strong> for your blade components.
                It's a visual playground where you can test blade components in isolation.
            </p>
            <p style="font-size:1rem;color:#9ca3af;margin:0 0 2.5rem;">
                Try it out by clicking a component in the sidebar.
            </p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;">
                {{-- Featured Components --}}
                <div>
                    <h3 style="font-size:1.1rem;font-weight:600;color:#111827;margin:0 0 1rem;padding-bottom:0.5rem;border-bottom:2px solid #e5e7eb;">Featured Components</h3>
                    <div style="display:flex;flex-direction:column;gap:0.25rem;">
                        @foreach(['accordion', 'alert', 'button', 'badge', 'card', 'drawer', 'modal', 'dropdown'] as $featured)
                            <a href="/component/{{ $featured }}" style="display:flex;align-items:center;gap:0.5rem;padding:0.6rem 0.75rem;color:#374151;text-decoration:none;font-size:0.95rem;border-radius:0.375rem;transition:background 0.15s;"
                               onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                                    <rect x="1" y="1" width="14" height="14" rx="3" stroke="#9ca3af" stroke-width="1.2"/>
                                    <rect x="4.5" y="4.5" width="7" height="7" rx="1.5" fill="#2563eb" opacity="0.2"/>
                                </svg>
                                <strong>{{ ucfirst($featured) }}</strong>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Info --}}
                <div>
                    <h3 style="font-size:1.1rem;font-weight:600;color:#111827;margin:0 0 1rem;padding-bottom:0.5rem;border-bottom:2px solid #e5e7eb;">How it Works</h3>
                    <div style="display:flex;flex-direction:column;gap:1rem;">
                        <div style="display:flex;gap:0.75rem;align-items:flex-start;">
                            <div style="width:28px;height:28px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0;">1</div>
                            <div>
                                <p style="margin:0;font-size:0.9rem;font-weight:500;color:#111827;">Select a Component</p>
                                <p style="margin:0.25rem 0 0;font-size:0.85rem;color:#6b7280;">Browse and click any component in the sidebar</p>
                            </div>
                        </div>
                        <div style="display:flex;gap:0.75rem;align-items:flex-start;">
                            <div style="width:28px;height:28px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0;">2</div>
                            <div>
                                <p style="margin:0;font-size:0.9rem;font-weight:500;color:#111827;">Edit Props & Slots</p>
                                <p style="margin:0.25rem 0 0;font-size:0.85rem;color:#6b7280;">Change values using the controls panel below the preview</p>
                            </div>
                        </div>
                        <div style="display:flex;gap:0.75rem;align-items:flex-start;">
                            <div style="width:28px;height:28px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0;">3</div>
                            <div>
                                <p style="margin:0;font-size:0.9rem;font-weight:500;color:#111827;">See Live Preview</p>
                                <p style="margin:0.25rem 0 0;font-size:0.85rem;color:#6b7280;">The component renders in real-time with your changes</p>
                            </div>
                        </div>
                        <div style="display:flex;gap:0.75rem;align-items:flex-start;">
                            <div style="width:28px;height:28px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0;">4</div>
                            <div>
                                <p style="margin:0;font-size:0.9rem;font-weight:500;color:#111827;">Copy the Code</p>
                                <p style="margin:0.25rem 0 0;font-size:0.85rem;color:#6b7280;">Use the Code tab to copy the generated Blade markup</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top:2.5rem;padding:1.25rem;background:#eff6ff;border-radius:0.75rem;border:1px solid #bfdbfe;">
                <p style="margin:0;font-size:0.9rem;color:#1e40af;">
                    <strong>Running in WebAssembly</strong> &mdash; This entire playground runs PHP 8.4 in your browser via WASM.
                    No server needed. All code execution is sandboxed and safe.
                </p>
            </div>
        </div>
    </div>
</x-layouts.studio>
