<template>
    <div v-if="user">
        <div>
            Вы будете зарегистрированы, как {{user['name']}}
        </div>
        <input type="hidden" v-for="(value, key) in user" :name="`user[${key}]`" :value="value">
    </div>
</template>

<script>
    export default {
        name: "Test",

        data(){
            return {
                user: null
            }
        },

        mounted() {
            this.$http.get('/test-data').then(({data}) => {
                this.user = data;
            });
        }
    }
</script>