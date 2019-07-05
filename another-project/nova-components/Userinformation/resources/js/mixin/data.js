export default {
    csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    TRANSITION_AUTH_REGISTER : 'register',
    TRANSITION_AUTH_CONFIRM : 'confirm',
    TRANSITION_AUTH_MODERATOR_CONFIRM : 'moder_confirm',
    TRANSITION_AUTH_DATA_CHANGE : 'change',
    TRANSITION_AUTH_DATA_CHANGE_CONFIRM : 'change_confirm',
    TRANSITION_AUTH_BACK_TO_CHANGE : 'change_return',
    TRANSITION_AUTH_BACK_TO_REGISTER : 'register_return'
};
