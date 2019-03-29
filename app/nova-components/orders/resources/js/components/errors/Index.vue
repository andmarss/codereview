<template>
    <div class="fixed pin-r pin-b w-1/2" v-bind:class="errors.length === 0 ? 'h-12' : 'h-auto'" v-if="errors.length > 0 && hasActiveErrors">
        <div v-for="error in errors">
            <Error :error="error"
                   @errorNotActive="errorNotActive" />
        </div>
    </div>
</template>

<script>
    import Error from './Error';

    export default {
        name: "Index",

        props: {
            errors: {
                type: Array,
                default: []
            }
        },

        methods: {
            errorNotActive(error){
                this.errors = this.errors.map(e => {
                    if(e.id === error.id){
                        return error;
                    } else {
                        return e;
                    }
                });
            }
        },

        computed: {
            hasActiveErrors(){
                return this.errors.some(error => {
                    return error.active
                })
            }
        },

        components: {
            Error
        }
    }
</script>
