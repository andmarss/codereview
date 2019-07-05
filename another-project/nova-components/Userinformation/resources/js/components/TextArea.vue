<template>
    <div class="flex border-b border-40">
        <div class="w-1/5 py-6 px-8">
            <label class="inline-block text-80 pt-2 leading-tight" :for="id">
                {{label}}
            </label>
        </div>
        <div class="py-6 px-8 w-4/5">
            <ckeditor
                :id="id"
                v-model="value"
                :toolbar="toolbar"
                :language="`ru`"
                :height="`100px`"

                @input="input"
            />
        </div>
    </div>
</template>

<script>
    import VueCkeditor from 'vueckeditor';

    export default {
        props: {
            value: {
                required: true,
                default: '',
                validator: prop => typeof prop === 'string' || typeof prop === 'number' || prop === null
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

            toolbar: {
                type: Array,
                default: [
                    ['Undo','Redo'],
                    ['Bold','Italic','Strike'],
                    ['NumberedList','BulletedList'],
                    ['Cut','Copy','Paste'],
                ]
            }
        },

        mounted(){
            if(this.value === null) {
                this.value = '';
            }
        },

        methods: {
            input(value){
                this.$emit('change', this.id, value);
            }
        },

        components: {
            ckeditor: VueCkeditor
        },
    }
</script>
