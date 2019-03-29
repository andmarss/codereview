<template>
    <li>
        <slot name="header" />

        <slot name="content"/>
    </li>
</template>

<script>
    import _ from 'lodash';

    export default {
        name: "Item",

        props: {
            item: {
                type: Object,
                default: {},
                required: true
            }
        },

        data(){
            return {
                active: false
            }
        },

        methods: {
            toggle(){
                this.active = !this.active;
            },

            isEmptyObject(object){
                return !_.isArray(object) && _.isObject(object) && _.isEmpty(object);
            }
        }
    }
</script>
