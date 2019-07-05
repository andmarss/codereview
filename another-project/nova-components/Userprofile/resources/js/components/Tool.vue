<template>
    <loading-view :loading="loading">
        <heading class="mb-3">{{__("Изменить профиль")}}</heading>

        <card class="overflow-hidden">
            <form @submit.prevent="saveProfile">
                <!-- Validation Errors -->
                <validation-errors :errors="validationErrors"/>

                <TextField
                    :placeholder="'Имя'"
                    :label="'Имя'"
                    :value="profile.name"
                    :id="'name'"
                    :type="'text'"
                    :required="true"

                    @change="change"
                />

                <TextField
                    :placeholder="'E-mail адрес'"
                    :label="'E-mail адрес'"
                    :value="profile.email"
                    :id="'email'"
                    :type="'text'"
                    :required="true"

                    @change="change"
                />

                <TextField
                    :placeholder="'Пароль'"
                    :label="'Пароль'"
                    :value="profile.password"
                    :id="'password'"
                    :type="'password'"

                    @change="change"
                />

                <TextField
                    :placeholder="'Подтверждение пароля'"
                    :label="'Подтверждение пароля'"
                    :value="profile.password_confirmation"
                    :id="'password_confirmation'"
                    :type="'password'"

                    @change="change"
                />

                <!-- Create Button -->
                <div class="bg-30 flex px-8 py-4">
                    <button class="ml-auto btn btn-default rounded-full btn-primary mr-3">
                        Сохранить
                    </button>
                </div>
            </form>
        </card>
    </loading-view>
</template>

<script>
    import { Errors, Minimum } from 'laravel-nova';
    import axios from 'axios';

    export default {

        data: () => ({
            loading: true,
            fields: [],
            validationErrors: new Errors(),
            clientTypes: [],
            clientType: null,

            csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            profile: {
                name: '',
                email: '',
                password: '',
                password_confirmation: ''
            }
        }),

        mounted() {
            this.getFieldsData();
        },

        methods: {
            /**
             * Get the available fields for the resource.
             */
            async getFieldsData() {
                /**
                 * Очищаем поля
                 */
                this.resetFields();
                /**
                 * Если пользователь - супер-пользователь
                 **/

                axios.get('/nova-vendor/userprofile').then(({data}) => {

                    this.profile.name = data.name;
                    this.profile.email = data.email;

                });

                this.loading = false
            },

            /**
             * Saves the user's profile
             */
            async saveProfile() {
                try {
                    this.loading = true;
                    await this.createRequest();
                    this.loading = false;

                    toastr.success('Ваши данные успешно изменены!');

                    // Reset the form by refetching the fields
                    await this.getFieldsData();

                    this.validationErrors = new Errors()
                } catch (error) {
                    this.loading = false
                    if (error.response.status == 422) {
                        this.validationErrors = new Errors(error.response.data.errors)
                    }
                }
            },

            /**
             * Send a create request to update the user's profile data
             */
            createRequest() {
                return axios.post('/nova-vendor/userprofile',
                    this.createResourceFormData(), {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });
            },

            /**
             * Create the form data for creating the resource.
             */
            createResourceFormData() {
                /**
                 * @type FormData
                 **/
                let fd = new FormData();

                fd.append('_token', this.csrf_token);

                if(this.profile.name){
                    fd.append('name', this.profile.name);
                }

                if(this.profile.email) {
                    fd.append('email', this.profile.email);
                }

                if(this.profile.password) {
                    fd.append('password', this.profile.password);
                }

                if(this.profile.password_confirmation) {
                    fd.append('password_confirmation', this.profile.password_confirmation);
                }

                return fd;
            },
            /**
             * обработчик события change у полей
             *
             * @param field
             * @param value
             */
            change(field, value){
                this.profile[field] = value;
            },
            /**
             * Очищает поля
             */
            resetFields(){
                this.profile.name = '';
                this.profile.email = '';
                this.profile.password = '';
                this.profile.password_confirmation = '';
                this.profile.client_type = '';
                this.profile.manager_password = '';
            }
        }
    }
</script>
