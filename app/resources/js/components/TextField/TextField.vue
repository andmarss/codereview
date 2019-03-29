<template>
    <div>
        <div class="flex border-b border-40">
            <div class="w-1/5 py-6 px-8">
                <label class="inline-block text-80 pt-2 leading-tight">
                    {{label}}
                </label>
            </div>
            <div class="py-6 px-8 w-4/5" v-if="type !== 'tel'">
                <input v-if="required"
                       required="required"
                       :id="id"
                       :type="type"
                       :placeholder="placeholder"
                       v-model="value"
                       @change="change"
                       class="w-full form-control form-input form-input-bordered">
                <input v-else
                       :id="id"
                       :type="type"
                       :placeholder="placeholder"
                       v-model="value"
                       @change="change"
                       class="w-full form-control form-input form-input-bordered">
                <div class="help-text help-text mt-2">

                </div>
            </div>
            <div class="py-6 px-8 w-4/5" v-else>
                <input v-if="required"
                       required="required"
                       :id="id"
                       :type="'text'"
                       :placeholder="placeholder"
                       v-mask="pattern"
                       v-model="value"
                       @change="change"
                       class="w-full form-control form-input form-input-bordered"/>
                <input v-else
                       :id="id"
                       :type="'text'"
                       :placeholder="placeholder"
                       v-mask="pattern"
                       v-model="value"
                       @change="change"
                       class="w-full form-control form-input form-input-bordered"/>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            type: {
                type: String,
                default: 'text'
            },

            value: {
                type: [String, Number],
                required: true,
                default: ''
            },

            label: {
                type: String,
                required: true
            },

            id: {
                type: String,
                required: true
            },

            placeholder: {
                type: String,
                default: 'Введите текст'
            },

            required: {
                type: Boolean,
                default: false
            },

            pattern: {
                type: String,
                default: '(###) ###-####'
            },

            usePatternPlaceholder: {
                type: Boolean,
                default: false
            }
        },

        methods: {
            change(){
                let field = this.id;

                this.$emit('change', field, this.value);
            }
        }
    }
</script>
