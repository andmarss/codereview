<template>
    <div class="w-full">
        <ul class="list-reset flex border-b border-grey">
            <li v-for="(tab) in tabs" class="mr-1" v-bind:class="tab.active ? '-mb-px' : ''">
                <a class="no-underline" v-bind:class="tab.active ?
                'bg-white inline-block border-grey border-l border-t border-r rounded-t py-2 px-4 text-blue-dark font-semibold' :
                'bg-white inline-block py-2 px-4 text-blue hover:text-blue-darker font-semibold'"
                   @click="select(tab)"
                   href="javascript:void(0)">{{tab.title}}</a>
            </li>
        </ul>
        <div>
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Tabs",

        data(){
            return {
                tabs: [],
            }
        },

        methods: {
            select(tab){
                this.tabs.forEach(t => {
                    t.active = (t === tab);
                });
            }
        },

        created(){
            this.tabs = this.$children;
        }
    }
</script>
