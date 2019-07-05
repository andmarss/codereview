<template>
    <div class="rounded-b" v-if="elements.length > 0 && totalPages > 1" v-bind:class="bgColorClass">
        <nav class="flex justify-between items-center">
            <!-- Previous Link -->
            <button
                v-if="!buttons"
                :disabled="!hasPrev()"
                class="btn btn-link py-3 px-4"
                :class="{
                    'text-primary dim': hasPrev(),
                    'text-80 opacity-50': !hasPrev(),
                }"
                rel="prev"
                @click.prevent="changePage( prevPage )"
            >
                Предыдущая
            </button>

            <span v-if="totalPages && !buttons" class="text-sm text-80">
                {{currentPage}} из {{totalPages}}
            </span>

            <ul class="flex w-full items-center list-reset justify-center py-3" v-if="totalPages && buttons">
                <li v-if="hasFirst()"
                    @click.prevent="changePage(1)"
                    class="rounded py-3 px-4 font-semibold hover:border-blue border-grey border mr-10 cursor-pointer"
                >
                    1
                </li>

                <li v-if="hasFirst() && hasFirstShowDots()" class="mr-10">...</li>

                <li class="rounded py-3 px-4 font-semibold hover:border-blue mr-10 cursor-pointer"
                v-for="page in pages"
                @click.prevent="changePage(page)"
                v-bind:class="parseInt(page) === parseInt(currentPage)
                ? 'border-blue border-2'
                : 'border-grey border'">
                    {{page}}
                </li>

                <li v-if="hasLast() && hasLastShowDots()" class="mr-10">...</li>

                <li v-if="hasLast()"
                    @click.prevent="changePage(totalPages)"
                    class="rounded py-3 px-4 font-semibold hover:border-blue border-grey border cursor-pointer"
                >
                    {{totalPages}}
                </li>
            </ul>

            <!-- Next Link -->
            <button
                v-if="!buttons"
                :disabled="!hasNext()"
                class="btn btn-link py-3 px-4"
                :class="{
                    'text-primary dim': hasNext(),
                    'text-80 opacity-50': !hasNext(),
                }"
                rel="next"
                @click.prevent="changePage( nextPage )"
            >
                Следующая
            </button>
        </nav>
    </div>
</template>

<script>
    export default {
        name: "Index.vue",

        props: {
            elements: {
                type: Array,
                default: [],
                required: true
            },
            total: {
                type: Number,
                default: 0,
                required: true
            },
            perPage: {
                type: Number,
                default: 5
            },
            currentPage: {
                type: Number,
                default: 1,
                required: true
            },
            range: {
                type: Number,
                default: 2
            },
            buttons: {
                type: Boolean,
                default: false
            },
            bgColorClass: {
                type: String,
                default: 'bg-grey-lighter'
            }
        },

        methods: {
            /**
             * Изменяет номер страницы
             * @param page
             */
            changePage(page){
                this.$emit('changePage', page);
            },
            /**
             * Возвращает true, если у текущей страницы есть предыдущая страница
             * Иначе - false
             * @return {boolean}
             */
            hasPrev(){
                return parseInt(this.currentPage) > 1;
            },
            /**
             * Возвращает true, если у текущей страницы есть следующая страница
             * Иначе - false
             * @return {boolean}
             */
            hasNext(){
                return parseInt(this.currentPage) < this.totalPages
            },
            /**
             * Возвращает true, если у текущей страницы есть ранжировка перед первой страницей
             * @return {boolean}
             * */
            hasFirst(){
                return parseInt(this.rangeStart) !== 1;
            },
            /**
             * Возвращает true, если у текущей страницы есть ранжировка перед последней страницей
             * @return {boolean}
             * */
            hasLast(){
                return parseInt(this.rangeEnd) < this.totalPages;
            },

            hasFirstShowDots(){
                return (1 - this.rangeStart) > 1;
            },

            hasLastShowDots(){
                return (this.totalPages - this.rangeEnd) > 1;
            }
        },

        computed: {
            /**
             * Возвращает номер следующей страницы
             * @return {number}
             */
            nextPage(){
                return parseInt(this.currentPage) + 1
            },
            /**
             * Возвращает номер предыдущей страницы
             * @return {number}
             */
            prevPage(){
                return parseInt(this.currentPage) - 1
            },
            /**
             * Возвращает общее количество страниц
             * @return {number}
             */
            totalPages(){
                return this.total ? Math.ceil(this.total / this.perPage) : 0;
            },
            /**
             * возвращает ранжировку чисел перед текущей страницей
             * @return {number}
             */
            rangeStart(){
                let start = parseInt(this.currentPage) - this.range;

                return start > 0 ? start : 1
            },
            /**
             * Возвращает ранжировку чисел после текущей страницы
             * @return {default.computed.totalPages|(function(): number)}
             */
            rangeEnd(){
                let end = parseInt(this.currentPage) + this.range;

                return end < this.totalPages ? end : this.totalPages
            },
            /**
             * Возвращает массив чисел для отображения кнопок пагинации
             * @return {Array}
             */
            pages(){
                let pages = [];

                for(let i = this.rangeStart; i <= this.rangeEnd; i++) {
                    pages.push(i);
                }

                return pages;
            }
        },
    }
</script>

<style scoped lang="scss">
    .mr-10 {
        margin-right: 10px;
    }
</style>
