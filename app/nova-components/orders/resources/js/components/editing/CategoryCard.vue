<template>
    <div>
        <div class="border-b border-grey-light p-4 cursor-pointer" @click.prevent="toggle()">
            <p class="font-bold text-sm">{{ (selected !== undefined && selected !== null) ? `${title}: ${selected}`: title }}</p>
        </div>

        <!--Выбор формата-->

        <div class="p-4 bg-grey-lightest" v-show="element.opened">
            <ul class="flex list-reset flex-wrap">
                <li v-for="list in lists"
                    class="p-2 cursor-pointer"
                    v-bind:class="`${(list.selected && isUsualList(list)) ?
                    'bg-blue' :
                    ''} ${list.slider ? 'w-full' : (type === 'number' ?
                    'w-full md:mb-3 sm:mb-3' : isUsualList(list) ?
                    'lg:w-1/3 md:w-1/2 sm:w-full hover:bg-blue hover:text-white md:mb-3 sm:mb-3' : 'w-full')}`">

                    <!-- Если элемент списка - не слайдер, и тип его поля - не number -->

                    <div v-if="isUsualList(list)" @click="selectList(list)">
                        <img class="w-full h-auto" :src="`/img/printphotobook/${list.value}.jpg`" alt="" />
                        <p class="font-semibold text-base" v-bind:class="list.selected ? 'color-white' : 'color-black'">{{list.value}}</p>
                    </div>

                    <!-- Если элемент списка - слайдер -->

                    <div v-else-if="list.slider">
                        <div class="my-4">
                            <p v-if="element.help" class="cursor-default mb-8" v-html="element.help"></p>
                            <slider
                                v-model="rangeValue"
                                :min="list.value.min"
                                :max="list.value.max"
                                :interval="list.value.step"
                                :value="list.value.min"
                                :lazy="true"
                                :show="element.opened"
                                @callback="change(list)"
                            />
                        </div>
                    </div>

                    <!-- Если элемент списка - не слайдер, но тип его поля - number -->

                    <div v-else-if="isBookNum(list)">
                        <p v-if="element.help" class="mb-8" v-html="element.help"></p>
                        <quantity
                            @selectList="selectList"
                            :list="list"
                            :value="value"
                        />
                    </div>

                    <!-- Если элемент списка - текстовое поле -->

                    <div v-else-if="isComment(list)">
                        <p v-if="element.help" class="mb-8" v-html="element.help"></p>
                        <textarea
                            class="w-full shadow-inner p-4 border-0 outline-none textarea"
                            placeholder="Комментарий"
                            rows="6"
                            @change="textAreaChange(list)"
                            ref="comment"
                        >{{value ? value : ''}}</textarea>
                    </div>

                    <!-- Если элемент списка - тех. информация -->

                    <div v-if="isTechInfo(list)">
                        <p v-if="element.help" class="mb-8" v-html="element.help"></p>
                        <div v-for="elem in list.value">
                            <div class="flex flex-wrap items-center mb-4" v-if="getType(elem) === 'object' && isTechInfoObject(elem)">
                                <p class="flex mr-2" v-if="elem.title">{{elem.title}}:</p>
                                <p class="flex mr-2"
                                    v-for="object in elem"
                                    v-if="getType(object) === 'object' && isTechInfoElement(object)"
                                >
                                    <a class="text-base no-underline hover:no-underline bordered" :href="object.href">{{object.text}}</a>
                                </p>
                            </div>
                            <p
                                class="mb-4"
                                v-if="getType(elem) === 'object' && isTechInfoElement(elem)">
                                <a class="text-xs no-underline hover:no-underline bordered" :href="elem.href">{{elem.text}}</a>
                            </p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';
    import slider from 'vue-slider-component';
    import quantity from './Quantity';

    export default {
        name: "CategoryCard",

        props: [
            'lists',
            'title',
            'categoryName',
            'element',
            'type',
            'value',
            'bookType',
            'bookCategory'
        ],

        data(){
            return {
                active: false,
                selected: null,
                rangeValue:5,
                commentValue: '',
                root: window.location.origin
            }
        },

        methods: {
            /**
             * см. ./Index.vue:203
             */
            toggle(){
                this.$emit('toggle', this.element)
            },
            /**
             * см. ./Index.vue:265
             */
            selectList(list){
                this.$emit('selectList', list);
            },
            /**
             * Изменяет заголовок карты
             */
            updateTitle(){
                if(this.lists && this.lists.length) {
                    this.lists.forEach(list => {
                        if(list.selected) this.selected = list.value;
                        // устанавливаем значение по умолчанию для слайдера
                        if(list.slider) {
                            if(this.value === undefined || this.value === null) {
                                list.value = list.value.min;
                                list.setDefaultValue = true;

                                this.$emit('selectList', list);
                            }

                            this.selected = this.value;
                            this.rangeValue = this.value;
                        }
                    });
                }
            },

            getType(type){
                return Object.prototype.toString.call(type).slice(8).slice(0,-1).toLowerCase();
            },
            /**
             * функция-обработчик для работы со слайдером
             * @param list
             */
            change(list){
                list.value = this.rangeValue;

                this.$emit('selectList', list);
            },
            /**
             * обработчик изменений в textarea
             * @param {object} list
             * */
            textAreaChange(list){
                list.value = this.$refs.comment[0].value;

                this.$emit('selectList', list);
            },
            /**
             * Является ли элемент списка - простым элементом с картинкой внутри
             * @param {object} list
             * @return {boolean}
             * */
            isUsualList(list){
                return !!(!list.slider && !['bookNum', 'comment', 'techInfo', 'isSample'].includes(list.category));
            },
            /**
             * является ли элемент - элементом количества книг
             * @param {object} list
             * @return {boolean}
             * */
            isBookNum(list) {
                return list.category === 'bookNum';
            },
            /**
             * Является ли элемент - текстовым полем
             * @param {object} list
             * @return {boolean}
             * */
            isComment(list) {
                if(list.category === 'comment'){
                    this.commentValue = this.value;

                    return true;
                }

                return false;
            },
            /**
             * Проверяет, передан ли элемент, содержащий объекты тех. информации
             * или нет
             * @param {object} list
             * @return {boolean}
             * */
            isTechInfo(list) {
                return list.category === 'techInfo';
            },
            /**
             * Элемент тех. информации передан или нет?
             * @param object
             * @return {boolean}
             */
            isTechInfoObject(object) {
                // если внутри объекта находятся объекты cover и page
                // и внутри каждого из них есть свойства text и href

                // !! впереди для преобразования в boolean
                return !!(
                    (object.cover && this.isTechInfoElement(object.cover))
                    &&
                    (object.page && this.isTechInfoElement(object.page))
                );
            },
            /**
             * У объектов тех. информации должны быть поля text и href
             * Эта функция проверяет, есть ли они у переданного объекта
             * @param elem
             * @return {boolean}
             */
            isTechInfoElement(elem){
                return !!(elem.href && elem.text);
            }
        },

        mounted(){
            this.updateTitle();
        },

        updated(){
            this.updateTitle();
        },

        components: {
            slider,
            quantity
        }
    }
</script>

<style scoped lang="scss">
    .textarea {
        &:focus {
            box-shadow: inset 0 0 6px rgba(0,0,0,.4);
            outline: none;
        }
    }

    .bordered {
        border-bottom: 1px solid #2779bd;
        color: #2779bd;
        padding-bottom: 0;

        &:hover {
            color: #1c3d5a;
            border-bottom-color: #1c3d5a;
        }
    }
</style>
