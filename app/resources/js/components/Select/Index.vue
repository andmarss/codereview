<template>
    <div>
        <div class="flex border-b border-40">
            <div class="w-1/5 py-6 px-8">
                <label class="inline-block text-80 pt-2 leading-tight" :for="id">
                    {{ label }}
                </label>
            </div>
            <div class="py-6 px-8 w-4/5">
                <select
                    :id="id"
                    v-model="value"
                    class="w-full form-control form-select"
                    @change="change"
                >
                    <option value="" selected>{{firstOptionText}}</option>

                    <option
                        v-if="options.length"
                        v-for="option in options"
                        :value="option.value"
                        :selected="option.value == value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            firstOptionText: {
                type: String,
                default: 'Выбрать вариант'
            },

            options: {
                type: Array,
                default: []
            },

            value: {
                type: [String, Number],
                required: true
            },

            id: {
                type: String,
                required: true
            },

            label: {
                type: String,
                required: true
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
