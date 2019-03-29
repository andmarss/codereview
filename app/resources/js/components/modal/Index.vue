<template>
    <div class="modal select-none fixed pin z-50 overflow-x-hidden overflow-y-auto" v-if="active">
        <div class="fixed pin bg-80 z-50 opacity-75 modal-bg"></div>
        <div class="relative mx-auto flex justify-center z-50 py-view">
            <div tabindex="-1"
                role="dialog"
                @v-if="active">
                <form
                    autocomplete="off"
                    @keydown="handleKeydown"
                    @submit.prevent.stop="handleConfirm"
                    class="bg-white rounded-lg shadow-lg overflow-hidden w-action"
                >
                    <div>
                        <heading :level="2" class="pt-8 px-8">{{ title }}</heading>

                        <p class="text-80 px-8" :class="showTextField ? 'my-4' : 'my-8'">
                            {{question}}
                        </p>
                        <div v-if="showTextField" class="my-4">
                            <div class="flex">
                                <div class="w-1/5 py-6 px-8" v-if="showTextField && textFieldLabel">
                                    <label class="inline-block text-80 pt-2 leading-tight">
                                        {{textFieldLabel}}
                                    </label>
                                </div>
                                <div class="py-6 px-8 w-4/5" v-if="showTextField && textFieldPlaceholder">
                                    <input :type="textFieldType"
                                           :placeholder="textFieldPlaceholder"
                                           v-model="textField"
                                           class="w-full form-control form-input form-input-bordered" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-30 px-6 py-3 flex">
                        <div class="flex items-center ml-auto">
                            <button
                                type="button"
                                @click.prevent="close"
                                class="btn text-80 font-normal h-9 px-3 mr-3 btn-link"
                            >
                                {{cancelButtonText ? cancelButtonText : 'Отменить'}}
                            </button>

                            <button
                                :disabled="working"
                                type="submit"
                                class="btn btn-default btn-primary"
                                @click.prevent="handleConfirm"
                            >
                                <loader v-if="working" width="30"></loader>
                                <span v-else>{{confirmButtonText ? confirmButtonText : 'Подтвердить'}}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import { mixin as clickaway } from 'vue-clickaway';

    export default {
        mixins: [clickaway],

        props: {
            working: Boolean,
            active: Boolean,
            resourceName: {},
            selectedAction: {},
            question: {
                type: String,
                required: true
            },
            title: {
                type: String,
                required: true
            },
            cancelButtonText: String,
            confirmButtonText: String,
            // Текстовое поле для ввода данных
            showTextField: {
                type: Boolean,
                required: false,
                default: false
            },
            textFieldLabel: {
                type: String,
                required: false,
                default: ''
            },
            textFieldPlaceholder: {
                type: String,
                required: false,
                default: ''
            },
            textFieldType: {
                type: String,
                required: false,
                default: 'text'
            }
        },

        data(){
            return {
                modalBg: null,
                textField: ''
            }
        },

        methods: {
            /**
             * Stop propogation of input events unless it's for an escape or enter keypress
             */
            handleKeydown(e) {
                if (['Escape', 'Enter'].indexOf(e.key) !== -1) {
                    return
                }

                e.stopPropagation()
            },

            /**
             * Execute the selected action.
             */
            handleConfirm() {
                this.$emit('confirm', this.textField);

                this.textField = '';
            },

            /**
             * Close the modal.
             */
            close() {
                this.$emit('close');
            },

            handleEscape(e) {
                e.stopPropagation();

                if (e.keyCode === 27) {
                    this.close();
                }
            },
        }
    }
</script>
