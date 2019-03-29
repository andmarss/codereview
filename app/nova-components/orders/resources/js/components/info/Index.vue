<template>
    <div class="w-full">
        <div class="bg-40 mb-4 py-3">
            <div class="flex justify-start items-center ml-auto mb-4">
                &#8592;&nbsp;&nbsp;<!-- <-\s\s -->
                <a href="javascript:void(0);"
                   class="text-black"
                   @click.prevent="goBack">Мои заказы</a>
                &nbsp;&nbsp;/&nbsp;&nbsp;<!-- \s\s/\s\s -->
                <heading :level="1" class="font-semibold">Заказ #{{ id }}</heading>
            </div>
        </div>

        <heading :level="3" class="flex mb-3 font-semibold">Информация о заказе #{{ id }}</heading>

        <Accordion :items="items" class="w-full rounded overflow-hidden shadow-lg mt-8" v-if="items.length > 0">
            <Item
                slot="accordion"
                slot-scope="{item, selectItem}"
                :item="item"
            >
                <div slot="header" @click="selectItem(item)">
                    <div class="border-b border-grey-light p-4 cursor-pointer">
                        <p class="font-bold text-sm">{{item.title}}</p>
                    </div>
                </div>

                <div slot="content" v-if="item.active" class="p-4 bg-grey-lightest" >
                    <div v-if="item.tag === 'p'" v-for="content in item.content">
                        <p v-for="(value, key) in content"
                           v-if="getType(value) === 'string' || getType(value) === 'number'"
                           class="mb-4"
                        >
                            <b>{{key}}:</b>&nbsp;&nbsp;{{value}}
                        </p>
                    </div>

                    <div v-else-if="item.tag === 'div'">
                        <div class="w-full flex flex-wrap mt-12 -mx-2">
                            <heading :level="3" class="flex font-semibold w-full mb-4 mx-2" v-if="content.name">Книга {{upperCaseFirst(content.name)}}</heading>
                            <div class="mb-8 mx-2 image-container"
                                 v-for="file in content.files"
                                 :style="`width: ${110 * file.sizeRate}px;`">

                                <div :class="file.url ? 'bg-transparent' : 'bg-grey'">
                                    <div class="relative w-full flex justify-center items-center">
                                        <img :src="file.url"
                                             :alt="file.title"
                                             v-show="file.url"
                                             :style="`width: ${110 * file.sizeRate}px; height: 110px;`" />
                                    </div>
                                    <p class="text-center">{{file.title}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Item>
        </Accordion>
    </div>
</template>

<script>
    import axios from 'axios';
    import Accordion from '../accordion/Index';
    import Item from '../accordion/Item';
    import UserOrdersMixin from '../../mixin';

    export default {
        name: "Index",

        props: ['id'],

        mixins: [UserOrdersMixin],

        data(){
            return {
                routePattern: /printphotobook\/info\/\d+/ig,

                items: []
            }
        },

        methods: {
            isInfoPage(){
                return window.location.pathname.match(this.routePattern);
            },
            /**
             * @param data
             */
            fillInfoPage(data){

                if(data.info && Object.keys(data.info).length > 0) {
                    this.items.push(
                        this.convertDealInfoItem(data)
                    );
                }

                if(data.books && data.books.length > 0) {
                    this.items.push(
                        this.convertDealLayoutsItem(data)
                    );
                }
            }
        },

        mounted(){
            if(this.isInfoPage()) {
                axios.get(`/api/photobook/info/${this.id}`).then(({data}) => {
                    if(data.access){
                        this.fillInfoPage(data);
                    } else {
                        this.goBack();
                    }
                })
            } else {
                this.goBack();
            }
        },

        components: {
            Accordion,
            Item
        }
    }
</script>
