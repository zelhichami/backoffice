@extends('layouts.app')

{{-- Add specific styles if needed --}}
@push('styles')
    {{-- Added specific styles from user code --}}
    <style>
        .code-block {
            background-color: #f3f4f6; /* gray-100 */
            border: 1px solid #e5e7eb; /* gray-200 */
            border-radius: 0.375rem; /* rounded-md */
            padding: 1rem;
            margin-top: 0.5rem;
            margin-bottom: 1.5rem;
            overflow-x: auto;
            font-family: monospace;
            font-size: 0.875rem; /* text-sm */
            color: #1f2937; /* gray-800 */
        }
        .code-block code {
            white-space: pre;
        }
        h3 {
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-size: 1.125rem; /* text-lg */
            font-weight: 600;
        }
        h2 {
            font-size: 1.5rem; /* text-2xl */
            font-weight: 600; /* font-semibold */
            margin-bottom: 1rem; /* mb-4 */
            color: #3b97eb; /* text-[#3b97eb] */
        }
        p {
            margin-bottom: 0.5rem;
            color: #4b5563; /* gray-600 */
        }
    </style>
@endpush

@section('content')

    <div class="bg-[#f9f9fb] min-h-screen py-16 px-6 md:px-12 font-sans">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-semibold text-[#003870] mb-6">Liquid Reference</h1>
            <p class="text-[#444] mb-12 text-lg leading-relaxed">
                This is a comprehensive guide to all built-in Liquid objects used to access, display, and manipulate data within sections. Liquid enables dynamic output of objects and their properties, which can be further modified using tags for logic or filters for direct alterations.            </p>

            <div class="mb-12">


                <div class="space-y-6 space-x-4  ">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-semibold text-[#3b97eb] mb-4">Referencing objects</h2>
                        <span class="text-sm text-gray-500"></span>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium">@{{ }}<span class="pl-2 text-sm text-gray-500 ">Double curly brace delimiters denote an output.</span></h3>
                        </div>
                        <hr>
                        <p class="text-gray-600 mb-4 whitespace-pre-line leading-relaxed">
                            Liquid objects represent variables that we can use to build the sections. Object types include landing page resources, standard xPage content, and functional elements that help us to build interactivity. Objects might represent a single data point, or contain multiple properties. Some properties might represent a related object, such as a product in a landing page.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-[#282c34] text-white rounded-md p-4 font-mono text-sm">
                                <div class="mb-1 text-gray-400">Code</div>
                                <code class="text-[#8be9fd]"> <span class="text-gray-400">{% # product.title -> Health potion %}</span>
                                    </br>
                                    </br>
                                    <span >@{{ product.title }}</span>
                                </code>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 font-mono text-sm">
                                <div class="mb-1 text-gray-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L21 10.5m0 0l-3.75 3.75M21 10.5H3" />
                                    </svg>
                                    Output
                                </div>
                                <code class="text-[#1c1c1e]">Health potion</code>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="space-y-4 space-x-4 mt-12">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-semibold text-[#3b97eb] mb-4">Defining logic with tags</h2>
                        <span class="text-sm text-gray-500"></span>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium">{% %}<span class="pl-2 text-sm text-gray-500 ">Curly brace percentage delimiters denote logic and control flow.</span></h3>
                        </div>
                        <hr>
                        <p class="text-gray-600 mb-4 whitespace-pre-line leading-relaxed">
                            Liquid tags are used to define logic that tells templates what to do. Text within tag delimiters doesn’t produce visible output when the webpage is rendered.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-[#282c34] text-white rounded-md p-4 font-mono text-sm">
                                <div class="mb-1 text-gray-400">Code</div>
                                <code class="text-[#8be9fd]">{% if product.title == 'Health potion' %}</br>
                                    <span class="ml-4">This is a rare potion. Use it sparingly!</span></br>
                                    {% endif %}</code>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 font-mono text-sm">
                                <div class="mb-1 text-gray-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L21 10.5m0 0l-3.75 3.75M21 10.5H3" />
                                    </svg>
                                    Output
                                </div>
                                <code class="text-[#1c1c1e]">This is a rare potion. Use it sparingly!</code>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="space-y-6 space-x-4 mt-12 ">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-semibold text-[#3b97eb] mb-4">Modifying output with filters</h2>
                        <span class="text-sm text-gray-500"></span>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium">@{{ | }}<span class="pl-2 text-sm text-gray-500 ">Filters are placed within an output tag and denoted by a pipe character.</span></h3>
                        </div>
                        <hr>
                        <p class="text-gray-600 mb-4 whitespace-pre-line leading-relaxed">
                            Liquid filters are used to modify the output of variables and objects. To apply filters to an output, add the filter and any filter parameters within the output's curly brace delimiters, preceded by a pipe character.                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-[#282c34] text-white rounded-md p-4 font-mono text-sm">
                                <div class="mb-1 text-gray-400">Code</div>
                                <code class="text-[#8be9fd]"> <span class="text-gray-400">{% # product.title -> Health potion %}</span>
                                    </br>
                                    </br>
                                    <span class="">@{{ product.title | upcase | remove: 'HEALTH' }}</span>
                                </code>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 font-mono text-sm">
                                <div class="mb-1 text-gray-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L21 10.5m0 0l-3.75 3.75M21 10.5H3" />
                                    </svg>
                                    Output
                                </div>
                                <code class="text-[#1c1c1e]"> POTION</code> {{-- Adjusted Output --}}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 space-x-4  mt-12">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-semibold text-[#3b97eb] mb-4">Variable tags</h2>
                        <span class="text-sm text-gray-500"></span>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium">assign<span class="pl-2 text-sm text-gray-500 ">Variable tags enable you to create new Liquid variables.</span></h3>
                        </div>
                        <hr>
                        <p class="text-gray-600 mb-4 whitespace-pre-line leading-relaxed">
                            Predefined Liquid objects can be overridden by variables with the same name. To make sure that you can access all Liquid objects, make sure that your variable name doesn't match a predefined object's name.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-[#282c34] text-white rounded-md p-4 font-mono text-sm">
                                <div class="mb-1 text-gray-400">Code</div>
                                <code class="text-[#8be9fd]">
                                    {% assign product_title = product.title | upcase %}
                                    <br>
                                    <br>
                                    @{{ product_title }}
                                </code>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 font-mono text-sm">
                                <div class="mb-1 text-gray-500 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L21 10.5m0 0l-3.75 3.75M21 10.5H3" />
                                    </svg>
                                    Output
                                </div>
                                <code class="text-[#1c1c1e]">HEALTH POTION</code>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <br><hr>

            {{-- === GLOBAL OBJECTS === --}}

            <div class="mb-12 mt-8">
                <h1 class="text-4xl font-semibold text-[#003870] mt-12 mb-6 ">Objects List</h1>
                <p class="text-gray-700 mb-6">
                    Objects in Liquid represent the dynamic content that xPage expose to sections.
                    They are variables that give access to important information, such as customer data, products, cart contents, and more.

                    Each object can have multiple properties and can be used to create powerful, personalized experiences.
                </p>
                <div class="bg-[#f9f9fb] min-h-screen py-6 px-6 font-sans">


                    <div class="max-w-7xl mx-auto">
                        <h2 id="product" class="text-3xl font-semibold text-[#3b97eb] mb-4">Product <span class="bg-white text-sm text-gray-500 border-gray-300 border-b-2 p-1">object</span></h2>
                        <p class="text-[#444] mb-12 text-lg leading-relaxed">
                            A <a href="https://myxpage.shop/products" class=" text-[#003870] hover:text-primary underline" target="_blank">product</a> in the store.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                            <div x-data="productObject">
                                <p class="text-[#003870] font-bold"> Properties</p>
                                <template x-for="(property, index) in properties"  :key="index">
                                    <div class="border-b py-4 px-6">
                                        <button @click="toggle(index)" class="w-full text-left flex justify-between items-center">
                                            <div>
                                                <div class="">
                                                    <span class="text-md font-semibold" x-text="property.name"></span>
                                                    <span class="text-sm text-gray-500" x-html="'(' + property.type +')'"></span>
                                                </div>

                                            </div>
                                            <svg :class="{'rotate-45': expandedIndex === index}" class="h-4 w-4 transform transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"  d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>

                                        <div x-show="expandedIndex === index" class="pl-4" x-collapse.duration.500ms>
                                            <p class="text-sm text-gray-600 mt-3" x-text="property.description"></p>
                                            <div class="bg-[#282c34] text-white rounded-md p-4 font-mono text-sm mt-2 pl-4">
                                                <div class="mb-1 text-gray-400">Example</div>
                                                <code class="text-[#8be9fd]" x-html="property.example"></code>
                                            </div>
                                        </div>

                                    </div>
                                </template>
                                <div x-data="productFilters">
                                    <p class="text-[#003870] font-bold mt-4 "> Filters</p>
                                    <template x-for="(property, index) in properties"  :key="index">
                                        <div class="border-b py-4 px-6">
                                            <button @click="toggle(index)" class="w-full text-left flex justify-between items-center">
                                                <div>
                                                    <div class="">
                                                        <span class="text-md font-semibold" x-text="property.name"></span>
                                                        <span class="text-sm text-gray-500" x-html="'(' + property.type +')'"></span>
                                                    </div>

                                                </div>
                                                <svg :class="{'rotate-45': expandedIndex === index}" class="h-4 w-4 transform transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"  d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>

                                            <div x-show="expandedIndex === index" class="pl-4" x-collapse.duration.500ms>
                                                <p class="text-sm text-gray-600 mt-3" x-text="property.description"></p>
                                                <div class="bg-[#282c34] text-white rounded-md p-4 font-mono text-sm mt-2 pl-4">
                                                    <div class="mb-1 text-gray-400">Example</div>
                                                    <code class="text-[#8be9fd]" x-html="property.example"></code>
                                                </div>
                                            </div>

                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <div  class="space-y-4">
                                    <div class="flex space-x-4">
                                        <button  class="text-blue-600 border-b-2 border-blue-600 pb-2 font-medium">Object</button>
                                    </div>

                                    <div >
                                        <pre class="bg-gray-100 p-4 rounded-md overflow-x-auto text-sm font-mono text-gray-800">
{
    "id": "9e4dd5bb-fde8-45ea-b8c6-4dbf73ad7918",
    "slug": "curlpro-styler",
    "title": "CurlPro Styler",
    "description": "This eco-friendly bag is made",
    "page_title": null,
    "meta_description": null,
    "is_digital": false,
    "images": [],
    "image": {},
    "options": [
      "Size",
      "Color"
    ],
    "variants": [],
    "default": {},
    "category": null,
    "status": "ACTIVE",
    "created_at": 1740571355,
}

                                        </pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="max-w-7xl mx-auto mt-12">
                        <h2 id="image" class="text-3xl font-semibold text-[#3b97eb] mb-4">Image <span class="bg-white text-sm text-gray-500 border-gray-300 border-b-2 p-1">object</span></h2> {{-- Changed ID --}}
                        <p class="text-[#444] mb-12 text-lg leading-relaxed">
                            Represents an image associated with a product or variant.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                            <div x-data="imageObject">
                                <p class="text-[#003870] font-bold"> Properties</p>
                                <template x-for="(property, index) in properties" :key="index">
                                    <div class="border-b py-4">
                                        <button @click="toggle(index)" class="w-full text-left flex justify-between items-center">
                                            <div>
                                                <div class="">
                                                    <span class="text-md font-semibold" x-text="property.name"></span>
                                                    <span class="text-sm text-gray-500" x-html="'(' + property.type +')'"></span>
                                                </div>

                                            </div>
                                            <svg :class="{'rotate-45': expandedIndex === index}" class="h-4 w-4 transform transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"  d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>

                                        <div x-show="expandedIndex === index" class="pl-4" x-collapse>
                                            <p class="text-sm text-gray-600 mt-3" x-html="property.description"></p>
                                            <div class="bg-[#282c34] text-white rounded-md p-4 font-mono text-sm mt-2 pl-4">
                                                <div class="mb-1 text-gray-400">Example</div>
                                                <code class="text-[#8be9fd]" x-html="property.example"></code>
                                            </div>
                                        </div>

                                    </div>
                                </template>

                            </div>

                            <div>
                                <div  class="space-y-4">
                                    <div class="flex space-x-4">
                                        <button  class="text-blue-600 border-b-2 border-blue-600 pb-2 font-medium">Object</button>
                                    </div>

                                    <div >
                                        <pre class="bg-gray-100 p-4 rounded-md overflow-x-auto text-sm font-mono text-gray-800">
{
    "id": "9ecbb749-e742-41ac-9f74-1119e1ca5834",
    "name": "[image] - 558659.jpeg",
    "size": 871583,
    "alt": null,
    "order": 0,
    "mime_type": "image/jpeg",
    "url": "https://..."
}

                                        </pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="max-w-7xl mx-auto mt-12">
                        <h2 id="option" class="text-3xl font-semibold text-[#3b97eb] mb-4">Option <span class="bg-white text-sm text-gray-500 border-gray-300 border-b-2 p-1">object</span></h2> {{-- Changed ID --}}
                        <p class="text-[#444] mb-12 text-lg leading-relaxed">
                            Represents a product option, like 'Color' or 'Size'.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                            <div x-data="optionObject">
                                <p class="text-[#003870] font-bold"> Properties</p>
                                <template x-for="(property, index) in properties" :key="index">
                                    <div class="border-b py-4">
                                        <button @click="toggle(index)" class="w-full text-left flex justify-between items-center">
                                            <div>
                                                <div class="">
                                                    <span class="text-md font-semibold" x-text="property.name"></span>
                                                    <span class="text-sm text-gray-500" x-html="'(' + property.type +')'"></span>
                                                </div>

                                            </div>
                                            <svg :class="{'rotate-45': expandedIndex === index}" class="h-4 w-4 transform transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"  d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>

                                        <div x-show="expandedIndex === index" class="pl-4" x-collapse.duration.500ms>
                                            <p class="text-sm text-gray-600 mt-3" x-html="property.description"></p>
                                            <div class="bg-[#282c34] text-white rounded-md p-4 font-mono text-sm mt-2 pl-4">
                                                <div class="mb-1 text-gray-400">Example</div>
                                                <code class="text-[#8be9fd]" x-html="property.example"></code>
                                            </div>
                                        </div>

                                    </div>
                                </template>

                            </div>

                            <div>
                                <div  class="space-y-4">
                                    <div class="flex space-x-4">
                                        <button  class="text-blue-600 border-b-2 border-blue-600 pb-2 font-medium">Object</button>
                                    </div>

                                    <div >
                                        <pre class="bg-gray-100 p-4 rounded-md overflow-x-auto text-sm font-mono text-gray-800">
{
    "id": "9ecbc469-fe98-48e2-85a4-5f59f36f8db9",
    "name": "Color",
    "order": 0,
    "values": [
        {
            "id": "9ecbc469-ff9e-47a4-ba52-f39f48a5fee0",
            "value": "Black",
            "order": 1
        },
        {
            "id": "9ecbc469-ff4f-48de-8e8f-415cfb892ad4",
            "value": "Red",
            "order": 0
        }
    ]
}

                                        </pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="max-w-7xl mx-auto mt-12">
                        <h2 id="variant" class="text-3xl font-semibold text-[#3b97eb] mb-4">Variant <span class="bg-white text-sm text-gray-500 border-gray-300 border-b-2 p-1">object</span></h2> {{-- Changed ID --}}
                        <p class="text-[#444] mb-12 text-lg leading-relaxed">
                            Represents a specific variant (combination of options) of a product.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                            <div x-data="variantObject">
                                <p class="text-[#003870] font-bold"> Properties</p>
                                <template x-for="(property, index) in properties" :key="index">
                                    <div class="border-b py-4">
                                        <button @click="toggle(index)" class="w-full text-left flex justify-between items-center">
                                            <div>
                                                <div class="">
                                                    <span class="text-md font-semibold" x-text="property.name"></span>
                                                    <span class="text-sm text-gray-500" x-html="'(' + property.type +')'"></span>
                                                </div>

                                            </div>
                                            <svg :class="{'rotate-45': expandedIndex === index}" class="h-4 w-4 transform transition-transform duration-150" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"  d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>

                                        <div x-show="expandedIndex === index" class="pl-4" x-collapse.duration.500ms>
                                            <p class="text-sm text-gray-600 mt-3" x-html="property.description"></p>
                                            <div class="bg-[#282c34] text-white rounded-md p-4 font-mono text-sm mt-2 pl-4">
                                                <div class="mb-1 text-gray-400">Example</div>
                                                <code class="text-[#8be9fd]" x-html="property.example"></code>
                                            </div>
                                        </div>

                                    </div>
                                </template>

                            </div>

                            <div>
                                <div  class="space-y-4">
                                    <div class="flex space-x-4">
                                        <button  class="text-blue-600 border-b-2 border-blue-600 pb-2 font-medium">Object</button>
                                    </div>

                                    <div >
                                        <pre class="bg-gray-100 p-4 rounded-md overflow-x-auto text-sm font-mono text-gray-800">
{
    "id": "9ecbbd3e-1574-4c26-b10b-f24db226059a",
    "sku": null,
    "price": "22.00",
    "compare_price": "30.00",
    "quantity": null,
    "name": "Color:Red",
    "image": {},
    "values": [
        {
            "id": "9ecbbd3e-1237-47b8-9af1-8c5c3501bcec",
            "value": "Red",
            "order": 0,
            "option": {
                "id": "9ecbbd3e-117d-4332-95ab-cff619939610",
                "name": "Color",
                "order": 0
            }
        }
    ],
    "file": null
}

                                        </pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
                </div>
            </div>

        </div>
    </div>


    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productObject', () => ({
                expandedIndex: null,
                toggle(index) {
                    this.expandedIndex = this.expandedIndex === index ? null : index;
                },
                properties: [
                    { name: 'id', type: 'string (UUID)', description: 'The ID of the product.', example: '@{{ product.id }}' },
                    { name: 'slug', type: 'string', description: 'URL-friendly identifier for the product.', example: '@{{ product.slug }}' },
                    { name: 'title', type: 'string', description: 'Name of the product.', example: '@{{ product.title }}' },
                    { name: 'description', type: 'string | null', description: 'Detailed information about the product.', example: '@{{ product.description }}' },
                    { name: 'page_title', type: 'string | null', description: 'Custom title for the product page. Used as the HTML &lt;title&gt; tag, helpful for SEO.', example: '@{{ product.page_title }}' },
                    { name: 'meta_description', type: 'string | null', description: 'SEO description of the product.', example: '@{{ product.meta_description }}' },
                    { name: 'is_digital', type: 'boolean', description: 'Whether the product is digital or physical.', example: '{% if product.is_digital %}Digital{% endif %}' },
                    { name: 'images', type: 'array of <a class="text-[#003870] hover:text-primary underline" href="#image">image</a>', description: 'All images associated with the product.', example: '{% for image in product.images %}@{{ image.url }}{% endfor %}' },
                    { name: 'image', type: '<a class="text-[#003870] hover:text-primary underline" href="#image">image</a>', description: 'The primary/featured image object for the product.', example: '@{{ product.image.url }}' },
                    { name: 'variants', type: 'array of <a class="text-[#003870] hover:text-primary underline" href="#variant">variant</a>', description: 'Array of all product variants.', example: '{% for variant in product.variants %}@{{ variant.price }}{% endfor %}' },
                    { name: 'default', type: '<a class="text-[#003870] hover:text-primary underline" href="#variant">variant</a> | null', description: 'Default product variant (or first variant). Can be null.', example: '@{{ product.default.price }}' },
                    { name: 'options', type: 'array of <a class="text-[#003870] hover:text-primary underline" href="#option">option</a>', description: 'Array of product options (e.g., Size, Color).', example: '{% for option in product.options %}@{{ option.name }}{% endfor %}' },
                    { name: 'status', type: 'string', description: 'The publication status of the product (e.g., ACTIVE/INACTIVE).', example: '@{{ product.status }}' },
                    { name: 'created_at', type: 'timestamp', description: 'Timestamp when the product was created. Use the `date` filter to format.', example: '@{{ product.created_at | date: "%Y-%m-%d" }}' }
                ]
            })),
            Alpine.data('productFilters', () => ({
                expandedIndex: null,
                toggle(index) {
                    this.expandedIndex = this.expandedIndex === index ? null : index;
                },
                properties: [
                    {
                        name: 'is_product_available',
                        type: 'boolean',
                        description: 'Returns true if at least one of the product\'s variants is available. Returns false otherwise.',
                        example: '@{{ product | is_product_available }}'
                    },
                    {
                        name: 'first_available_variant',
                        type: '<a class="text-[#003870] hover:text-primary underline" href="#variant">variant</a>',
                        description: 'Returns the first available variant of the product.',
                        example: '@{% assign first_available_variant = product | first_available_variant %}</br>@{{ first_available_variant.quantity }}'
                    },
                    {
                        name: 'options',
                        type: 'array of string',
                        description: 'An array of the product\'s option names (e.g., Size, Color).',
                        example: '@{{ product | options }}'
                    },
                    {
                        name: 'min_price',
                        type: 'number',
                        description: 'The lowest price among all product variants, in the currency\'s smallest unit (e.g., cents).',
                        example: '@{{ product | min_price }}'
                    },
                    {
                        name: 'max_price',
                        type: 'number',
                        description: 'The highest price among all product variants, in the currency\'s smallest unit (e.g., cents).',
                        example: '@{{ product | max_price }}'
                    },
                    {
                        name: 'price_varies',
                        type: 'boolean',
                        description: 'Returns true if the product\'s variant prices differ. Returns false if all variants have the same price.',
                        example: '@{{ product | price_varies }}'
                    },
                    {
                        name: 'stock_tracked',
                        type: 'boolean',
                        description: 'Returns true if stock tracking is enabled for the product. Returns false otherwise.',
                        example: '@{{ product | stock_tracked }}'
                    },
                    {
                        name: 'allow_order_when_oos',
                        type: 'boolean',
                        description: 'Returns true if the product allows orders when out of stock. Returns false otherwise.',
                        example: '@{{ product | allow_order_when_oos }}'
                    }
                ]
            })),
            Alpine.data('imageObject', () => ({
                expandedIndex: null,
                toggle(index) { this.expandedIndex = this.expandedIndex === index ? null : index; },
                properties: [
                    { name: 'id', type: 'string (UUID)', description: 'The unique ID of the image.', example: '@{{ product.image.id }}' },
                    { name: 'name', type: 'string', description: 'Original filename of the image.', example: '@{{ product.image.name }}' },
                    { name: 'size', type: 'number', description: 'Size of the image file in bytes.', example: '@{{ product.image.size }}' },
                    { name: 'alt', type: 'string | null', description: 'Alt text description for the image (for SEO and accessibility).', example: '@{{ product.image.alt }}' },
                    { name: 'order', type: 'number', description: 'The display order of the image within a product\'s image gallery.', example: '@{{ product.image.order }}' },
                    { name: 'mime_type', type: 'string', description: 'The MIME type of the image (e.g., image/jpeg, image/png).', example: '@{{ product.image.mime_type }}' },
                    { name: 'url', type: 'string', description: 'The public URL to display the image.', example: '&lt;img src="@{{ product.image.url }}"&gt;' }
                ]
            })),
            Alpine.data('optionObject', () => ({
                expandedIndex: null,
                toggle(index) { this.expandedIndex = this.expandedIndex === index ? null : index; },
                properties: [
                    { name: 'id', type: 'string (UUID)', description: 'The unique ID of the product option.', example: '@{{ product.options.first.id }}' },
                    { name: 'name', type: 'string', description: 'The name of the option (e.g., "Color", "Size").', example: '@{{ product.options.first.name }}' },
                    { name: 'order', type: 'number', description: 'The display order of the option.', example: '@{{ product.options.first.order }}' },
                    { name: 'values', type: 'array of objects', description: 'An array of the possible values for this option.', example: '@{{ product.options.first.values | map: "value" | join: ", " }}' }
                ]
            })),
            Alpine.data('variantObject', () => ({
                expandedIndex: null,
                toggle(index) { this.expandedIndex = this.expandedIndex === index ? null : index; },
                properties: [
                    { name: 'id', type: 'string (UUID)', description: 'The unique ID of the variant.', example: '@{{ product.variants.first.id }}' },
                    { name: 'sku', type: 'string | null', description: 'The Stock Keeping Unit (SKU) of the variant.', example: '@{{ product.variants.first.sku }}' },
                    { name: 'price', type: 'string (formatted number)', description: 'The price of the variant. Use the `money` filter.', example: '@{{ product.variants.first.price | money }}' },
                    { name: 'compare_price', type: 'string (formatted number) | null', description: 'The compare-at price. Use the `money` filter.', example: '@{{ product.variants.first.compare_price | money }}' },
                    { name: 'quantity', type: 'number | null', description: 'The inventory quantity. May be null if not tracked.', example: '@{{ product.variants.first.quantity }}' },
                    { name: 'name', type: 'string', description: 'A concatenation of the variant\'s option values (e.g., "Color:Red").', example: '@{{ product.variants.first.name }}' },
                    { name: 'image', type: '<a class="text-[#003870] hover:text-primary underline" href="#image">image</a> | null', description: 'The specific image associated with this variant, if any.', example: '{% if product.variants.first.image %}&lt;img src="@{{ product.variants.first.image.url }}"&gt;{% endif %}' },
                    { name: 'values', type: 'array of objects', description: 'The specific option values that define this variant.', example: '{% for value_obj in product.variants.first.values %}@{{ value_obj.value }} {% endfor %}' },
                    { name: 'file', type: 'object | null', description: 'Details of a digital file attached to this variant (if applicable).', example: '@{{ product.variants.first.file.url }}' }
                ]
            }))
        });
    </script>

@endsection
