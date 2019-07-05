export default {
    data(){
        return {
            root: null,
            TRANSITION_AUTH_REGISTER : 'register',
            TRANSITION_AUTH_CONFIRM : 'confirm',
            TRANSITION_AUTH_MODERATOR_CONFIRM : 'moder_confirm',
            TRANSITION_AUTH_DATA_CHANGE : 'change',
            TRANSITION_AUTH_DATA_CHANGE_CONFIRM : 'change_confirm',

            csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            working: false
        }
    },

    methods: {
        /**
         * зарегестрирован ли пользователь (подтвердил ли на почте регистрацию)
         * @return {boolean}
         */
        isRegister(){
            return this.transitions.includes(this.TRANSITION_AUTH_CONFIRM);
        },
        /**
         * Подтверждена ли регистрация пользователя
         * @return {boolean}
         */
        isConfirmed(){
            return this.transitions.includes(this.TRANSITION_AUTH_MODERATOR_CONFIRM)
                ||
                this.transitions.includes(this.TRANSITION_AUTH_DATA_CHANGE)
                ||
                this.transitions.includes(this.TRANSITION_AUTH_DATA_CHANGE_CONFIRM);
        },
        /**
         * Подтвердил ли регистрацию модератор
         * @return {boolean}
         */
        isModeratorConfirmed(){
            return this.transitions.includes(this.TRANSITION_AUTH_DATA_CHANGE)
                ||
                this.transitions.includes(this.TRANSITION_AUTH_DATA_CHANGE_CONFIRM);
        },

        updateTransitions(data){
            Nova.mainStore.dispatch('updateTransitions', data.transitions);
        }
    },

    computed: {
        transitions(){
            return Nova.mainStore.getters.transitions;
        }
    }
}
