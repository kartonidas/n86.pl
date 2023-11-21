<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useRoute } from 'vue-router'
    import { useToast } from 'primevue/usetoast';
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import ProgressSpinner from 'primevue/progressspinner';
    
    import AppBreadcrumb from '@/layout/app/AppBreadcrumb.vue';
    import PermissionService from '@/service/PermissionService'
    import store from '@/store.js'
    
    export default {
        setup() {
            const route = useRoute()
            const permissionService = new PermissionService()
            const { t } = useI18n();
            const toast = useToast();
            
            return {
                t,
                v$: useVuelidate(),
                permissionService,
                route,
                toast
            }
        },
        data() {
            return {
                saving: false,
                loading: true,
                errors: [],
                permission: [],
                permission_items: [],
                modules: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.t('app.permissions'), route : { name : 'permissions'} },
                        {'label' : this.t('app.edit_group'), route : { name : 'permissions'}, disabled : true },
                    ],
                }
            }
        },
        updated() {
            if(store.getters.toastMessage) {
                let m = store.getters.toastMessage
                this.toast.add({ severity: m.severity, summary: m.summary, detail: m.detail, life: 3000 });
                store.commit('setToastMessage', null);
            }
        },
        mounted() {
            this.permissionService.modules()
                .then(
                    (response) => {
                        this.modules = response.data
                        for(var i in this.modules)
                            this.permission_items[this.modules[i].name] = []
                        
                        this.permissionService.get(this.route.params.permissionId)
                            .then(
                                (response) => {
                                    this.permission = response.data
                                    this.permission.is_default = this.permission.is_default == 1
                                    for (var p in this.permission.permissions) {
                                        for (var o in this.permission.permissions[p])
                                        {
                                            if (this.permission_items[p] == undefined)
                                                this.permission_items[p] = [];
                                            this.permission_items[p][this.permission.permissions[p][o]] = true
                                        }
                                    }
                                    this.loading = false
                                },
                                (errors) => {
                                    this.toast.add({ severity: 'error', summary: this.t('app.error'), detail: errors.response.data.message, life: 3000 });
                                }
                            );
                    },
                    (error) => {}
                );
        },
        methods: {
            async updateGroup() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    let permissions_text = ""
                    var items = [];
                    for (var perm in this.permission_items) {
                        if (this.permission_items[perm]) {
                            var operations = []
                            for (var op in this.permission_items[perm]) 
                                operations.push(op);
                            
                            if(operations.length)
                                items.push(perm + ':' + operations.join(','));
                        }
                    }
                    if(items.length)
                        permissions_text = items.join(';');
                        
                    this.permissionService.update(this.route.params.permissionId, this.permission.name, permissions_text, this.permission.is_default).then(
                        (response) => {
                            this.toast.add({ severity: 'success', summary: this.t('app.success'), detail: this.t('app.group_updated'), life: 3000 });
                            this.saving = false;
                            this.loading = false;
                        },
                        (errors) => {
                            if (errors.response.data.errors != undefined)
                                this.getErrors(errors.response.data.errors)
                            else
                                this.errors.push(errors.response.data.message)
                            this.saving = false
                        }
                    )
                }
            },
            getCheckboxInputId(a, b) {
                return "permission-" + a + "-" + b
            },
            getErrors(errors) {
                for (var i in errors) {
                    errors[i].forEach((err) => {
                        this.errors.push(err);
                    });
                }
            }
        },
        validations () {
            return {
                permission: {
                    name: { required },
                }
            }
        },
        components: {
            "Breadcrumb": AppBreadcrumb,
            "ProgressSpinner": ProgressSpinner,
        }
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <div class="mb-4">
                    <div class="p-fluid">
                        <div class="formgrid grid">
                            <div class="field col-12">
                                <label for="name" class="block text-900 font-medium mb-2">{{ $t('app.name') }}</label>
                                <InputText id="name" type="text" :placeholder="$t('app.name')" class="w-full" :class="{'p-invalid' : v$.permission.name.$error}" v-model="permission.name" :disabled="loading" />
                                <div v-if="v$.permission.name.$error">
                                    <small class="p-error">{{ v$.permission.name.$errors[0].$message }}</small>
                                </div>
                            </div>
                            
                            <div class="field col-12">
                                <div class="field-checkbox mb-0">
                                    <Checkbox inputId="defaultCheck" name="is_default" value="1" v-model="permission.is_default" :binary="true" :disabled="loading"/>
                                    <label for="defaultCheck">{{ $t('app.default') }}</label>
                                </div>
                            </div>
                            
                            <div v-for="module in modules" class="field col-12 sm:col-6 md:col-3">
                                <div class="mb-2"><strong class="mb-3">{{ $t('app.' + module.name) }}</strong></div>
                                <div class="field-checkbox mb-1 mt-1" v-for="op in module.perm.operation">
                                    <Checkbox :inputId="getCheckboxInputId(module.name, op)" value="1" v-model="permission_items[module.name][op]" :binary="true" :disabled="loading"/>
                                    <label :for="getCheckboxInputId(module.name, op)">
                                        {{ $t('app.' + op) }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <Message severity="error" :closable="false" v-if="errors.length">
                    <ul class="list-unstyled">
                        <li v-for="error of errors">
                            {{ error }}
                        </li>
                    </ul>
                </Message>
                
                <div class="" v-if="loading">
                    <ProgressSpinner style="width: 25px; height: 25px"/>
                </div>
                
                <Button :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" @click="updateGroup" class="w-auto text-center"></Button>
            </div>
        </div>
    </div>
    <Toast />
</template>