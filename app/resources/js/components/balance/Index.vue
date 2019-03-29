<template>
    <span>{{balance}}&nbsp;&#8381;</span>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "Index",

        methods: {
            update(){
                axios.get('/api/get-balance').then(({data}) => {
                    custom.store.dispatch('updateBalance', data.balance);
                })
            }
        },

        created(){
            this.update();
        },

        computed: {
            balance(){
                return custom.store.getters.balance;
            }
        }
    }
</script>
