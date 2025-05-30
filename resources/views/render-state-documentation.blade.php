@extends('layouts.app')

@push('styles')
    <style>
        /* Styles adapted from variables.blade.php and customized for this doc page */
        .doc-page-container {
            background-color: #f9f9fb; /* Consistent with variables.blade.php body */
            min-height: 100vh;
            padding-top: 4rem; /* py-16 */
            padding-bottom: 4rem; /* py-16 */
            padding-left: 1.5rem; /* px-6 */
            padding-right: 1.5rem; /* px-6 */
        }
        @media (min-width: 768px) {
            .doc-page-container {
                padding-left: 3rem; /* md:px-12 */
                padding-right: 3rem; /* md:px-12 */
            }
        }
        .doc-main-title {
            font-size: 2.25rem; /* text-4xl */
            font-weight: 600; /* font-semibold */
            color: #003870; /* Consistent with variables.blade.php h1 */
            margin-bottom: 1.5rem; /* mb-6 */
        }
        .doc-intro-text {
            color: #444444; /* text-[#444] from variables.blade.php */
            margin-bottom: 3rem; /* mb-12 */
            font-size: 1.125rem; /* text-lg */
            line-height: 1.75; /* leading-relaxed */
        }
        .doc-section {
            margin-bottom: 3rem; /* More spacing between major sections */
        }
        .doc-section-title { /* For h2 elements like "Quantity Management" */
            font-size: 1.875rem; /* text-3xl, slightly larger for clarity */
            font-weight: 600; /* font-semibold */
            color: #3b97eb; /* text-[#3b97eb] from variables.blade.php h2 */
            margin-bottom: 1.5rem; /* mb-6 */
            padding-bottom: 0.75rem; /* pb-3 */
            border-bottom: 1px solid #e2e8f0; /* border-gray-200 */
        }
        .doc-method-title { /* For h3 elements like method signatures */
            font-size: 1.25rem; /* text-xl */
            font-weight: 600; /* font-semibold */
            color: #1e293b; /* text-slate-800 for good contrast */
            margin-top: 2rem; /* More spacing above each method */
            margin-bottom: 0.75rem; /* mb-3 */
        }
        .doc-method-title code { /* Styling for the method signature itself */
            background-color: #e0f2fe; /* bg-sky-100 for light emphasis */
            color: #0c4a6e; /* text-sky-800 */
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 1.1rem; /* Slightly larger method name */
        }
        .doc-text, .doc-list li {
            color: #374151; /* text-gray-700 for better readability */
            margin-bottom: 0.5rem; /* mb-2 */
            line-height: 1.6;
        }
        .doc-inline-code { /* For inline code like parameter names, types */
            background-color: #e5e7eb; /* bg-gray-200 */
            color: #be185d; /* text-pink-700 for distinction */
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 0.875rem; /* text-sm */
        }
        .doc-parameters-title, .doc-returns-title, .doc-behavior-title {
            font-weight: 600;
            color: #1f2937; /* text-gray-800 */
            margin-top: 1rem; /* mt-4 */
            margin-bottom: 0.25rem; /* mb-1 */
        }
        .doc-parameters-list dt {
            font-family: monospace;
            font-weight: 500;
            color: #111827; /* text-gray-900 */
        }
        .doc-parameters-list dt .param-type {
            color: #059669; /* text-emerald-600 */
            font-style: italic;
        }
        .doc-parameters-list dd {
            margin-left: 1.5rem; /* ml-6 */
            margin-bottom: 0.5rem; /* mb-2 */
            color: #4b5563; /* text-gray-600 */
        }
        .doc-returns-text {
            margin-top: 0.25rem; /* mt-1 */
            color: #4b5563; /* text-gray-600 */
        }
        .doc-behavior-list {
            list-style-type: disc;
            list-style-position: inside;
            margin-left: 1rem; /* ml-4 */
            color: #4b5563; /* text-gray-600 */
        }
        .doc-method-entry {
            background-color: #ffffff; /* bg-white */
            padding: 1.5rem; /* p-6 */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1); /* shadow-md */
            margin-bottom: 2rem; /* mb-8 */
        }
    </style>
@endpush

@section('content')
    <div class="doc-page-container">
        <div class="max-w-4xl mx-auto">
            <header class="mb-12">
                <h1 class="doc-main-title">RenderState Class Documentation</h1>
                <p class="doc-intro-text">
                    A comprehensive guide to the <code class="doc-inline-code">RenderState</code> class, its methods, and their usage for managing UI state in sections.
                </p>
            </header>

            <div class="doc-section">
                <h2 class="doc-section-title">Quantity Management</h2>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>setQuantity(categoryId, quantity)</code></h3>
                    <p class="doc-text">Updates the quantity for a specific category and triggers change listeners.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">categoryId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The category identifier.</dd>
                        <dt><code class="doc-inline-code">quantity</code> (<span class="param-type">number</span>):</dt>
                        <dd>The new quantity value.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">RenderState</code> instance (for method chaining).</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>getQuantity(categoryId)</code></h3>
                    <p class="doc-text">Retrieves the current quantity for a category.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">categoryId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The category identifier.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">number</code> - Current quantity (default: 1 if not set).</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>getQuantityUpdateHandler(categoryId)</code></h3>
                    <p class="doc-text">Generates an event handler function for quantity input changes.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">categoryId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The category identifier.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">function</code> - Event handler that updates quantity from <code class="doc-inline-code">e.target.value</code>.</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>bindQuantityInput(categoryId, input)</code></h3>
                    <p class="doc-text">Binds a quantity input element to state management.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">categoryId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The category identifier.</dd>
                        <dt><code class="doc-inline-code">input</code> (<span class="param-type">HTMLInputElement|null</span>):</dt>
                        <dd>Input element to bind (auto-detected if null).</dd>
                    </dl>
                    <p class="doc-behavior-title">Behavior:</p>
                    <ul class="doc-behavior-list">
                        <li>Syncs initial value from input.</li>
                        <li>Sets up change listener for bidirectional updates.</li>
                        <li>Logs error if element not found.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-section">
                <h2 class="doc-section-title">Variant Management</h2>
                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>setVariantId(categoryId, variantId)</code></h3>
                    <p class="doc-text">Updates the selected variant ID for a category and triggers change listeners.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">categoryId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The category identifier.</dd>
                        <dt><code class="doc-inline-code">variantId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The new variant ID.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">RenderState</code> instance (for method chaining).</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>getVariantId(categoryId)</code></h3>
                    <p class="doc-text">Retrieves the current variant ID for a category.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">categoryId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The category identifier.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">string|null</code> - Current variant ID or null if not set.</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>getVariantUpdateHandler(categoryId)</code></h3>
                    <p class="doc-text">Generates an event handler function for variant selection changes.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">categoryId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The category identifier.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">function</code> - Event handler that updates variant ID from <code class="doc-inline-code">e.target.value</code>.</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>bindVariantSelect(categoryId, select)</code></h3>
                    <p class="doc-text">Binds a variant select element to state management.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">categoryId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The category identifier.</dd>
                        <dt><code class="doc-inline-code">select</code> (<span class="param-type">HTMLSelectElement|null</span>):</dt>
                        <dd>Select element to bind (auto-detected if null).</dd>
                    </dl>
                    <p class="doc-behavior-title">Behavior:</p>
                    <ul class="doc-behavior-list">
                        <li>Syncs initial value from select.</li>
                        <li>Sets up change listener for updates.</li>
                        <li>Logs error if element not found.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-section">
                <h2 class="doc-section-title">Cart Management</h2>
                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>getCart()</code></h3>
                    <p class="doc-text">Retrieves a copy of the current cart state.</p>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">Array</code> - Deep clone of cart items.</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>addToCart(categoryId, checkout)</code></h3>
                    <p class="doc-text">Adds item to cart and synchronizes with server.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">categoryId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>The category identifier.</dd>
                        <dt><code class="doc-inline-code">checkout</code> (<span class="param-type">boolean</span>):</dt>
                        <dd>Whether to redirect to checkout (default: false).</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">Promise&lt;Object&gt;</code> - Resolves with:</p>
                    <ul class="doc-behavior-list">
                        <li><code class="doc-inline-code">cart</code>: Updated cart array.</li>
                        <li><code class="doc-inline-code">checkout_url</code>: Checkout URL (if checkout=true).</li>
                    </ul>
                    <p class="doc-behavior-title">Behavior:</p>
                    <ul class="doc-behavior-list">
                        <li>Maintains optimistic UI updates.</li>
                        <li>Rolls back changes on server errors.</li>
                        <li>Triggers error handlers on failure.</li>
                    </ul>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>removeFromCart(variantId)</code></h3>
                    <p class="doc-text">Removes item from cart and synchronizes with server.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">variantId</code> (<span class="param-type">string|number</span>):</dt>
                        <dd>Variant ID to remove.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">Promise&lt;Array&gt;</code> - Updated cart array.</p>
                    <p class="doc-behavior-title">Behavior:</p>
                    <ul class="doc-behavior-list">
                        <li>Maintains optimistic UI updates.</li>
                        <li>Rolls back changes on server errors.</li>
                        <li>Triggers error handlers on failure.</li>
                    </ul>
                </div>
            </div>

            <div class="doc-section">
                <h2 class="doc-section-title">Event Handling</h2>
                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>registerChangeListener(property, callback)</code></h3>
                    <p class="doc-text">Registers a callback for state changes.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">property</code> (<span class="param-type">string</span>):</dt>
                        <dd>Property to watch ('quantity', 'variant', or 'cart').</dd>
                        <dt><code class="doc-inline-code">callback</code> (<span class="param-type">function</span>):</dt>
                        <dd>Callback to execute on changes.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">function</code> - Unsubscribe function.</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>removeChangeListener(property, callback)</code></h3>
                    <p class="doc-text">Removes a registered change listener.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">property</code> (<span class="param-type">string</span>):</dt>
                        <dd>Watched property.</dd>
                        <dt><code class="doc-inline-code">callback</code> (<span class="param-type">function</span>):</dt>
                        <dd>Registered callback function.</dd>
                    </dl>
                </div>
            </div>

            <div class="doc-section">
                <h2 class="doc-section-title">Error Handling</h2>
                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>pushError(message, timeout)</code></h3>
                    <p class="doc-text">Triggers error handlers with an error message.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">message</code> (<span class="param-type">string</span>):</dt>
                        <dd>Error message to display.</dd>
                        <dt><code class="doc-inline-code">timeout</code> (<span class="param-type">number</span>):</dt>
                        <dd>[Not implemented] Theoretical display duration.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">RenderState</code> instance.</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>defineErrorHandler(callback)</code></h3>
                    <p class="doc-text">Registers a global error handler.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">callback</code> (<span class="param-type">function</span>):</dt>
                        <dd>Error handler function.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">RenderState</code> instance.</p>
                </div>

                <div class="doc-method-entry">
                    <h3 class="doc-method-title"><code>removeErrorHandler(callback)</code></h3>
                    <p class="doc-text">Removes a registered error handler.</p>
                    <p class="doc-parameters-title">Parameters:</p>
                    <dl class="doc-parameters-list">
                        <dt><code class="doc-inline-code">callback</code> (<span class="param-type">function</span>):</dt>
                        <dd>Previously registered handler.</dd>
                    </dl>
                    <p class="doc-returns-title">Returns:</p>
                    <p class="doc-returns-text"><code class="doc-inline-code">RenderState</code> instance.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
