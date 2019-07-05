<template>
    <svg xmlns="http://www.w3.org/2000/svg"
         class="fill-current"
         :width="width"
         :height="height"
         :viewBox="viewBox"
         :aria-labelledby="type"
         role="presentation"
    >
        <path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" v-if="type === 'view'"/>
        <path d="M11 14.59V3a1 1 0 0 1 2 0v11.59l3.3-3.3a1 1 0 0 1 1.4 1.42l-5 5a1 1 0 0 1-1.4 0l-5-5a1 1 0 0 1 1.4-1.42l3.3 3.3zM3 17a1 1 0 0 1 2 0v3h14v-3a1 1 0 0 1 2 0v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3z" class="heroicon-ui" v-if="type === 'download'"/>
        <path fill-rule="nonzero" d="M14.32 12.906l5.387 5.387a1 1 0 0 1-1.414 1.414l-5.387-5.387a8 8 0 1 1 1.414-1.414zM8 14A6 6 0 1 0 8 2a6 6 0 0 0 0 12z" v-if="type === 'search'"/>
    </svg>
</template>

<script>
    export default {
        props: {
            type: {
                type: String,
                default: 'delete',
            },
            viewBox: {
                type: String,
                default: '0 0 20 20',
            },
            width: {
                type: [Number, String],
                default: 20,
            },
            height: {
                type: [Number, String],
                default: 20,
            },
        }
    }
</script>
