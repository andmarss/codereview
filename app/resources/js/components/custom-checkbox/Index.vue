<template>
    <label class="checkbox-container" @click.prevent="change">
        <input type="checkbox" name="agree" class="custom-checkbox" :checked="active ? 'check' : ''" />
        <span class="custom-checkbox-checkmark"></span>
        <p class="text-base" v-if="text">{{text}}
            <span v-if="subtext" class="text-grey-dark">&nbsp;&nbsp;{{subtext}}</span>
        </p>
    </label>
</template>

<script>
    export default {
        props: {
            active: {
                type: Boolean,
                default: false,
                required: true
            },

            text: String,

            subtext: String,
            element: {}
        },

        methods: {
            change(){
                this.$emit('change', this.element);
            }
        }
    }
</script>

<style scoped lang="scss">
    .checkbox-container {
        display: block;
        position: relative;
        padding-left: 35px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 22px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .custom-checkbox-checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 24px;
        width: 24px;
        background-color: #eee;
        border-radius: 50%;
    }

    .checkbox-container input ~ .custom-checkbox-checkmark {
        background-color: #ccc;
    }

    .checkbox-container input:checked ~ .custom-checkbox-checkmark {
        background-color: #2196F3;
    }

    .custom-checkbox-checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    .checkbox-container input:checked ~ .custom-checkbox-checkmark:after {
        display: block;
    }

    .checkbox-container .custom-checkbox-checkmark:after {
        top: 8px;
        left: 8px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
    }
</style>
