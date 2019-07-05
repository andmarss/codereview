<template>
    <div>
        <span class="fa fa-star" v-bind:class="intRate >= 1 ? 'checked' : ''"></span>
        <span class="fa fa-star" v-bind:class="intRate >= 2 ? 'checked' : ''"></span>
        <span class="fa fa-star" v-bind:class="intRate >= 3 ? 'checked' : ''"></span>
        <span class="fa fa-star" v-bind:class="intRate >= 4 ? 'checked' : ''"></span>
        <span class="fa fa-star" v-bind:class="intRate === 5 ? 'checked' : ''"></span>
    </div>
</template>

<script>
    export default {
        props: {
            rate: {
                type: [String, Number],
                required: true
            }
        },

        computed: {
            /**
             * Возвращает чесловое значение рейтинга
             * @return {number}
             */
            intRate(){
                return parseInt(this.rate);
            }
        }
    }
</script>

<style scoped>
    .checked {
        color: orange;
    }
</style>
