<template>
    <ul class="list-reset" v-if="items.length > 0">
        <slot name="accordion"
              v-for="item in items"
              :item="item"
              :selectItem="selectItem"></slot>
    </ul>
</template>

<script>
    import Item from './Item';

    export default {
        name: "Index",

        props: {
            items: {
                required: true,
                type: Array,
                default: []
            }
        },

        methods: {
            selectItem(item){
                if (item.active) return;

                this.items = this.items.map(i => {
                    if(i === item){
                        i.active = true;
                    } else {
                        i.active = false;
                    }

                    return i;
                });
            }
        },

        components: {
            Item
        }
    }
</script>
