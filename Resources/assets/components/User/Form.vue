<template>
	<div class="row">
    <div class="col-sm-12">

        <!--begin::Portlet-->
        <div class="m-portlet m-portlet--tab">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon m--hide">
                            <i class="fa fa-gear"></i>
                        </span>
                        <h3 class="m-portlet__head-text">
                            Module Form
                        </h3>
                    </div>
                </div>
            </div>

            <form class="m-form m-form--fit m-form--label-align-right" @submit.prevent="submit($event)">
                <div class="m-portlet__body">
                    <div class="col-md-5 offset-md-4" v-if="updated.status">
                        <div class="alert alert-dismissible fade show" :class="{'alert-info': updated.code == 200, 'alert-danger': updated.code != 200}">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                            {{updated.message}}
                        </div>
                    </div>
                    <div class="col-md-5 offset-md-4" v-if="Object.keys(errors).length > 0">
                        <div class="alert alert-danger">
                            <ul v-for="(error, key) in errors" :key="key">
                                <li v-for="(item, index) in error" :key="index">{{item}}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group m-form__group d-md-flex">
                        <div class="col-md-4 d-md-flex justify-content-end align-items-center py-3">
                            <label for="exampleInputEmail1">Name<span class="ml-1 m--font-danger" aria-required="true">*</span> :</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control m-input" name="name" placeholder="Full Name" v-model="data.user.name">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-md-flex">
                        <div class="col-md-4 d-md-flex justify-content-end align-items-center py-3">
                            <label for="exampleInputEmail1">Email<span class="ml-1 m--font-danger" aria-required="true">*</span> :</label>
                        </div>
                        <div class="col-md-8">
                            <input type="email" class="form-control m-input" name="email" placeholder="User Email" v-model="data.user.email">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-md-flex">
                        <div class="col-md-4 d-md-flex justify-content-end align-items-center py-3">
                            <label for="exampleInputEmail1">Password<span class="ml-1 m--font-danger" aria-required="true">*</span> :</label>
                        </div>
                        <div class="col-md-8">
                            <input type="password" class="form-control m-input" name="password" placeholder="User Password">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-md-flex">
                        <div class="col-md-4 d-md-flex justify-content-end align-items-center py-3">
                            <label for="exampleInputEmail1">Re-enter Password<span class="ml-1 m--font-danger" aria-required="true">*</span> :</label>
                        </div>
                        <div class="col-md-8">
                            <input type="password" class="form-control m-input" name="password_confirmation" placeholder="Password Confirmation">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end align-items-center py-3">
                            <label for="exampleInputEmail1">Role<span class="ml-1 m--font-danger" aria-required="true">*</span> :</label>
                        </div>
                        <div class="col-md-8">
                            <select name="role_id" class="form-control m-input m-input--solid">
                                <option value="" :selected="Object.keys(data.user.role).length == 0" disabled>-- Select One --</option>
                                <option :value="role.encrypted_id" v-for="(role, index) in data.roles" :key="index"  :selected="data.user.role.primary_key != undefined && role[data.user.role.primary_key] == data.user.role[data.user.role.primary_key]">{{role.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="m-portlet__foot m-portlet__foot--fit">
                    <div class="m-form__actions">
                        <div class="offset-md-4">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </form>

             <loading
                :is-full-page="true"
                :active.sync="loading"/>

            <!--end::Form-->
        </div>

        <!--end::Portlet-->

    </div>
  </div>
</template>
<script>
    import Loading from 'vue-loading-overlay'
    import FormComponent from '^/Core/Resources/assets/components/Component.vue'

    export default {
        components: {
            Loading,
            FormComponent
        },
        data(){
            return{
                data: {
                    user: {
                        role: {
                        }
                    },
                    roles: []
                },
                loading: false,
                updated: {
                    status: false,
                    code: 0,
                    message: ''
                },
                errors: {}
            }
        },
        created() {
          this.$parent.$data.breadcumb = `<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
                                            <li class="m-nav__item m-nav__item--home">
                                                <a href="#" class="m-nav__link m-nav__link--icon">
                                                    <i class="m-nav__link-icon la la-home"></i>
                                                </a>
                                            </li>
                                            <li class="m-nav__separator">-</li>
                                            <li class="m-nav__item">
                                                <a href="" class="m-nav__link">
                                                    <span class="m-nav__link-text">Home</span>
                                                </a>
                                            </li>
                                            <li class="m-nav__separator">-</li>
                                            <li class="m-nav__item">
                                                <a href="" class="m-nav__link">
                                                    <span class="m-nav__link-text">User</span>
                                                </a>
                                            </li>
                                        </ul>`;
        },
        mounted(){
            const self = this;
            let data;
            
            if(this.$route.query.code != undefined){
                data = {'code': this.$route.query.code}
            }else{
                data = {}
            }

            self.loading = true;
            axios({
                    method: "post",
                    url: '/control/user/show',
                    data: data
                })
                .then(response => {
                    self.data = response.data.data;
                    self.loading = false;
                })
                // eslint-disable-next-line
                .catch(errors => {
                    //Handle Errors
                })
        },
        methods: {
            submit(e){
                const formData = new FormData(e.target);

                if(this.$route.query.code != undefined){
                    formData.append('_method', 'PUT');
                    formData.append(this.data.user.primary_key, this.$route.query.code);
                }

                const self = this;

                self.loading = true;
                axios({
                    method: "post",
                    url: "/control/user/form",
                    data: formData,
                })
                .then(function (response) {
                    //handle success
                    self.updated = response.data;
                    self.loading = false;

                    if(response.data.status){
                        self.$router.push({
                            name: 'user-master',
                            params: { updated: response.data }
                        })
                    }else{
                        window.scrollTo(0, 0);
                        setTimeout(() => {
                            self.$set(self.updated, 'status', false);
                        }, 2000);
                    }

                })
                .catch(function (error) {
                    //handle error
                    self.loading = false;
                    self.errors = error.response.data.errors
                });
            }
        },
    }
</script>
<style >
</style>
<style lang="scss" scoped>
    @import 'vue-loading-overlay/dist/vue-loading.css';
</style>