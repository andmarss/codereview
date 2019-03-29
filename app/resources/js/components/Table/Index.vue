<template>
    <div>
        <div class="relative mr-6 my-4" v-if="useSearch">
            <input type="text"
                   class="bg-purple-white shadow rounded border
                   pl-12 pr-3 py-3 outline-none border-grey-light focus:border-grey"
                   placeholder="Введите email"
                   v-model="email"
                   @input.prevent="input()"
            >
            <div class="absolute pin-l pin-t mt-3 ml-4 text-purple-lighter loop">
                <Icon
                    :type="`search`"
                    :width="40"
                    :height="40"
                    :viewBox="`0 0 40 40`"
                />
            </div>
        </div>
        <table
            v-if="elements.length > 0"
            class="table w-full"
            cellpadding="0"
            cellspacing="0"
        >
            <thead>
            <tr>
                <th class="w-8">&nbsp;</th>

                <!-- Заголовки -->
                <th
                    v-for="value in titles"
                    class="text-center"
                >

                <span>
                    {{ value }}
                </span>
                </th>

                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <tableRow
                v-for="element in elements"
                :element="element"
                :fields="fields[element.id]"
                :structure="tdStructure"
                :key="element.id"
            />
            </tbody>
        </table>
    </div>
</template>

<script>
    import tableRow from './TableRow';
    import Icon from '../Icon';
    import _ from 'lodash';

    export default {
        name: "Index",

        props: {
            elements: {
                type: Array,
                required: true
            },
            titles: {
                type: Array,
                required: true
            },
            fields: {
                type: Object,
                required: true
            },
            tdStructure: {
                type: Array,
                required: true
            },
            useSearch: Boolean,
            searchDelay: {
                type: Number,
                default: 500
            }
        },

        data(){
            return {
                email: ''
            }
        },

        computed: {
            input(){
                let searchDelay = this.searchDelay;

                return _.debounce(function () { this.$emit('search', this.email) }, searchDelay);
            },
        },

        components: {
            tableRow,
            Icon
        },
    }
</script>

<style scoped lang="scss">
    .loop {
        color: black;
        opacity: .3;
    }
</style>
