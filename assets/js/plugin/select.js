'use strict';

function Select(Alpine) {
    require('Vendor/nasext/dependent-select-box/client-side/dependentSelectBox');

    let watchedComponentIds = [];
    let autoincrement = 0;

    window.addEventListener('resize', () => {
        watchedComponentIds.forEach(cid => {
            Alpine.store(cid).recalculateOptionsPosition();
        });
    });

    Alpine.data('select', () => ({
        cid: null,
        select: {
            ['x-init']() {
                this.cid = this.$el.getAttribute('data-cid');

                this.$watch(`$store.${this.cid}.opened`, (() => {
                    this.$refs.button.focus();
                }));
            }
        },
        selectButton: {
            ['x-ref']: 'button',
            ['x-on:click']() {
                if (!this.$event.target.hasAttribute('data-remove-button')) {
                    this.$store[this.cid].toggle();
                }
            },
            [':aria-haspopup']() {
                return 'listbox';
            },
            [':aria-expanded']() {
                return this.$store[this.cid].opened;
            }
        },
    }));

    Alpine.data('selectOptions', () => ({
        cid: null,
        selectOptions: {
            ['x-init']() {
                this.cid = this.$el.getAttribute('data-cid');

                this.$watch(`$store.${this.cid}.activeIndex`, (() => {
                    this.$store[this.cid].opened && (null !== this.$store[this.cid].activeIndex ? this.$store[this.cid].activeDescendant = this.$el.getElementsByTagName('ul')[0].children[this.$store[this.cid].activeIndex].id : this.$store[this.cid].activeDescendant = '')
                }));

                this.$watch(`$store.${this.cid}.selected`, (() => {
                    if (!this.$store[this.cid].multiple) {
                        return;
                    }

                    this.$nextTick((() => {
                        this.$store[this.cid].recalculateOptionsPosition();
                    }));
                }));

                this.$watch(`$store.${this.cid}.opened`, ((opened) => {
                    if (!opened) {
                        this.$nextTick((() => {
                            this.$store[this.cid].searchbarValue = '';
                        }));

                        return;
                    }

                    this.$nextTick((() => {
                        this.$el.focus();

                        if (!this.$store[this.cid].multiple) {
                            const children = this.$el.getElementsByTagName('ul')[0].children;
                            let child = children[this.$store[this.cid].activeIndex + 1];

                            if (!child) {
                                child = children[this.$store[this.cid].activeIndex];
                            }

                            if (child) {
                                child.scrollIntoView({
                                    block: 'nearest'
                                });
                            }
                        }

                        if (this.$refs.searchbar) {
                            this.$refs.searchbar.focus();
                        }
                    }));
                }));
            },
            ['x-transition:enter']: '',
            ['x-transition:enter-start']: '',
            ['x-transition:enter-end']: '',
            ['x-transition:leave']: '',
            ['x-transition:leave-start']: '',
            ['x-transition:leave-end']: '',
            ['x-on:click.outside']() {
                if (!this.$event.target.closest(`[data-cid="${this.cid}"]`)) {
                    this.$store[this.cid].close();
                }
            },
            ['x-on:keydown.escape.prevent.stop']() {
                this.$store[this.cid].close();
            },
            ['x-on:focusin.window']() {
                !this.$el.contains(this.$event.target)
                && !this.$event.target.closest(`[data-cid="${this.cid}"]`)
                && this.$store[this.cid].close();
            },
            [':aria-activedescendant']() {
                return this.cid ? this.$store[this.cid].activeDescendant : '';
            },
            ['x-show']() {
                return this.$store[this.cid].opened;
            },
            ['x-id']: '["selectOptions"]',
        },
    }));

    Alpine.directive('select', (el, {modifiers}, { cleanup }) => {
        autoincrement++;
        const cid = 'selectPluginState_' + autoincrement;
        let searchbar = '';
        let buttonText;

        if (-1 !== modifiers.indexOf('tags') && el.multiple) {
            buttonText = `
                <span class="flex flex-wrap -mb-2.5 sm:-mb-1.5">
                    <template x-for="(option, index) in $store.${cid}.options" :key="option.value">
                        <span x-show="$store.${cid}.isSelected(index)" class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-indigo-100 text-indigo-800 mr-1.5 mb-1.5 h-full">
                            <span x-html="option.html"></span>
                            <button type="button" data-remove-button class="r-0.5 pl-1.5" x-on:click="$store.${cid}.choose(index)">
                                &times;
                            </button>
                        </span>
                    </template>
                    <span class="mb-1.5 py-0.5 h-full">&nbsp;</span>
                </span>
            `;
        } else {
            buttonText = `<span class="block truncate" x-html="$store.${cid}.selectText || '&nbsp'"></span>`;
        }

        if (-1 !== modifiers.indexOf('searchbar')) {
            searchbar = `
                <div class="select-none relative py-2 px-3">
                    <input x-ref="searchbar" x-model="$store.${cid}.searchbarValue" type="text" placeholder="..." class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full text-sm border-gray-300 rounded-md disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none">
                </div>
            `;
        }

        const selectEl = document.createElement('div');
        selectEl.innerHTML = `
            <button x-bind="selectButton" type="button" class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                ${buttonText}
                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </button>
        `;

        selectEl.setAttribute('x-data', 'select');
        selectEl.setAttribute('x-bind', 'select');
        selectEl.setAttribute('data-cid', cid);
        selectEl.setAttribute('class', 'relative');

        const optionsEl = document.createElement('div');
        optionsEl.innerHTML = `
            <div class="flex flex-col pointer-events-auto bg-white shadow-lg rounded-md py-1 ring-1 ring-black ring-opacity-5">
                ${searchbar}
                <ul class="max-h-60 overflow-auto" role="listbox">
                    <template x-for="(option, index) in $store.${cid}.options" :key="option.value">
                        <li x-show="!$store.${cid}.searchbarValue.trim().length || -1 !== option.label.search(new RegExp($store.${cid}.searchbarValue.trim(), 'i'))" x-on:click="$store.${cid}.choose(index)" x-on:mouseenter="$store.${cid}.activeIndex = index" x-on:mouseleave="$store.${cid}.activeIndex = null" :id="$id('selectOptions') + index" class="cursor-pointer select-none relative py-2 pl-3 pr-9 mx-1 rounded" :class="{'text-white': $store.${cid}.activeIndex === index, 'text-gray-900': $store.${cid}.activeIndex !== index, 'bg-indigo-600': $store.${cid}.activeIndex === index}" role="option">
                            <span x-html="option.html" class="text-left font-normal block truncate"></span>
    
                            <span x-show="$store.${cid}.isSelected(index)" class="absolute inset-y-0 right-0 flex items-center pr-2 text-indigo-600" :class="{'text-white': $store.${cid}.activeIndex === index, 'text-indigo-600': $store.${cid}.activeIndex !== index}">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </li>
                    </template>
                </ul>
            </div>
        `;

        optionsEl.setAttribute('x-data', 'selectOptions');
        optionsEl.setAttribute('x-bind', 'selectOptions');
        optionsEl.setAttribute('data-cid', cid);
        optionsEl.setAttribute('class', 'absolute flex flex-col justify-end pointer-events-none z-10 mt-1 w-full text-base focus:outline-none sm:text-sm');
        optionsEl.setAttribute('tabindex', '-1');

        Alpine.store(cid, {
            cid: cid,
            el: el,
            selectEl: selectEl,
            optionsEl: optionsEl,
            opened: false,
            multiple: el.multiple || false,
            selected: [],
            options: [],
            selectText: '',
            activeIndex: '',
            activeDescendant: '',
            searchbarValue: '',

            init() {
                const buildOptions = () => {
                    this.selected = [];
                    this.options = [];

                    for (let opt of this.el.options) {
                        const option = {
                            value: opt.value,
                            label: opt.innerText,
                            html: opt.hasAttribute('data-html') ? opt.getAttribute('data-html') : opt.innerText,
                        };

                        this.options.push(option);

                        if (opt.selected) {
                            this.selected.push(this.options.length -1);
                        }
                    }

                    this.updateSelectText();
                };

                buildOptions();

                // dependent select box
                if (this.el.hasAttribute('data-dependentselectbox')) {
                    $(this.el).dependentSelectBox(buildOptions);
                }
            },

            isSelected(index) {
                return -1 !== this.selected.indexOf(index);
            },

            recalculateOptionsPosition() {
                const bounds = this.selectEl.getBoundingClientRect();
                const left = Math.round(window.scrollX + bounds.left);
                let top = Math.round(window.scrollY + bounds.top + this.selectEl.offsetHeight);
                let flexDirection = 'column';

                const optionsVisibility = this.optionsEl.style.visibility;
                const optionsDisplay = this.optionsEl.style.display;
                const documentHeight = document.documentElement.scrollHeight;

                this.optionsEl.style.visibility = 'hidden';
                this.optionsEl.style.display = 'block';
                this.optionsEl.style.height = 'auto';

                if ((documentHeight - top) < (this.optionsEl.offsetHeight + 10)) {
                    top = top - this.optionsEl.offsetHeight - this.selectEl.offsetHeight - (4 * 2);
                    flexDirection = 'column-reverse';
                }

                const height = this.optionsEl.offsetHeight;

                this.optionsEl.style.visibility = optionsVisibility;
                this.optionsEl.style.display = optionsDisplay;

                this.optionsEl.style.position = 'absolute';
                this.optionsEl.style.width = this.selectEl.offsetWidth + 'px';
                this.optionsEl.style.left = left + 'px';
                this.optionsEl.style.top = top + 'px';
                this.optionsEl.style.height = height + 'px';

                this.optionsEl.children[0].style.flexDirection = flexDirection;
            },

            toggle() {
                if (this.opened) {
                    return this.close();
                }

                this.recalculateOptionsPosition();

                this.opened = true;

                if (!this.multiple) {
                    this.activeIndex = this.selected.length ? this.selected.slice(0, 1)[0] : null;
                }
            },

            close(focusAfter) {
                if (!this.opened) {
                    return;
                }

                this.opened = false;

                focusAfter && focusAfter.focus();
            },

            active() {
                return this.options[this.activeIndex];
            },

            choose(index) {
                if (!this.multiple) {
                    this.selected = [index];
                    this.updateSelectText();
                    this.updateSelectValue();
                    this.close();

                    return;
                }

                const selected = this.selected;
                const indexOfIndex = selected.indexOf(index);

                if (-1 === indexOfIndex) {
                    selected.push(index);
                } else {
                    delete selected[indexOfIndex];
                }

                this.selected = selected.sort();
                this.updateSelectText();
                this.updateSelectValue();
            },

            updateSelectText() {
                const labels = [];

                for (let i in this.selected) {
                    labels.push(this.options[this.selected[i]].html);
                }

                this.selectText = labels.join(', ');
            },

            updateSelectValue() {
                const values = [];

                for (let i in this.selected) {
                    values.push(this.options[this.selected[i]].value);
                }

                for (let opt of this.el.options) {
                    opt.selected = -1 !== values.indexOf(opt.value);
                }

                //this.el.dispatchEvent(new Event('change'));
                $(this.el).trigger('change');
            },

            cleanup() {
                this.selectEl.remove();
                this.optionsEl.remove();
                watchedComponentIds = watchedComponentIds.filter(cid => cid !== this.cid);
            }
        });

        const previousDisplayValue = el.style.display;

        el.style.display = 'none';
        el.insertAdjacentElement('beforebegin', selectEl);
        document.body.insertAdjacentElement('beforeend', optionsEl);

        watchedComponentIds.push(cid);

        cleanup(() => {
            el.style.display = previousDisplayValue;
            Alpine.store(cid).cleanup();
        });
    });
}

export default Select;
