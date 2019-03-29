<template>
    <div v-show="active">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        props: {
            selected: {
                default: false
            },
            title: {
                required: true,
                type: String
            }
        },
        data(){
            return {
                active: false
            }
        },
        mounted(){
            this.active = this.selected;
        }
    }
</script>
