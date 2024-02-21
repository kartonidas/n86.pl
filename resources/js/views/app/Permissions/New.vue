<script>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import { getResponseErrors, hasAccess, setMetaTitle } from '@/utils/helper'
    
    import { appStore } from '@/store.js'
    import PermissionService from '@/service/PermissionService'
    
    export default {
        setup() {
            setMetaTitle('meta.title.permissions_new')
            
            const router = useRouter()
            const permissionService = new PermissionService()
            
            return {
                v$: useVuelidate(),
                permissionService,
                router
            }
        },
        data() {
            return {
                loading: true,
                saving: false,
                errors: [],
                permission: [],
                modules: [],
                permission_items: [],
                meta: {
                    breadcrumbItems: [
                        {'label' : this.$t('menu.users'), disabled : true },
                        {'label' : this.$t('menu.permissions'), route : { name : 'permissions'} },
                        {'label' : this.$t('permissions.new'), route : { name : 'permissions'}, disabled : true },
                    ],
                }
            }
        },
        beforeMount() {
            this.permissionService.modules()
                .then(
                    (response) => {
                        this.modules = response.data
                        for(var i in this.modules)
                            this.permission_items[this.modules[i].name] = []
                        this.loading = false
                    },
                    (response) => {
                        this.errors = getResponseErrors(response)
                    }
                );
        },
        methods: {
            async createGroup() {
                const result = await this.v$.$validate()
                if (result) {
                    this.saving = true
                    this.errors = []
                    
                    let permissions_text = ""
                    var items = [];
                    for (var perm in this.permission_items) {
                        if (this.permission_items[perm]) {
                            var operations = []
                            for (var op in this.permission_items[perm]) {
                                if (this.permission_items[perm][op])
                                    operations.push(op);
                            }
                            
                            if(operations.length)
                                items.push(perm + ':' + operations.join(','));
                        }
                    }
                    if(items.length)
                        permissions_text = items.join(';');
                        
                    this.permissionService.create(this.permission.name, permissions_text, this.permission.is_default)
                        .then(
                            (response) => {
                                appStore().setToastMessage({
                                    severity : 'success',
                                    summary : this.$t('app.success'),
                                    detail : this.$t('permissions.added'),
                                });
                                
                                if(hasAccess('permission:update'))
                                    this.router.push({name: 'permission_edit', params: { permissionId : response.data }})
                                else
                                    this.router.push({name: 'permissions'})
                            },
                            (response) => {
                                this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                        )
                } else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            getCheckboxInputId(a, b) {
                return "permission-" + a + "-" + b
            },
        },
        validations () {
            return {
                permission: {
                    name: { required },
                }
            }
        },
    }
</script>

<template>
    <Breadcrumb :model="meta.breadcrumbItems"/>
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid pt-4">
                <Help show="user" mark="user:permission" class="text-right mb-3"/>
                <form v-on:submit.prevent="createGroup" class="sticky-footer-form">
                    <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
                        <ul class="list-unstyled">
                            <li v-for="error of errors">
                                {{ error }}
                            </li>
                        </ul>
                    </Message>
                    
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12 mb-4">
                                    <label for="name" class="block text-900 font-medium mb-2" v-required>{{ $t('permissions.name') }}</label>
                                    <InputText id="name" type="text" :placeholder="$t('permissions.name')" class="w-full" :class="{'p-invalid' : v$.permission.name.$error}" v-model="permission.name" :disabled="loading || saving"/>
                                    <div v-if="v$.permission.name.$error">
                                        <small class="p-error">{{ v$.permission.name.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12 mb-4">
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="defaultCheck" name="is_default" value="1" v-model="permission.is_default" :binary="true" :disabled="loading || saving"/>
                                        <label for="defaultCheck">{{ $t('permissions.default') }}</label>
                                    </div>
                                </div>
                                
                                <div v-for="module in modules" class="field col-12 sm:col-6 md:col-3">
                                    <div class="mb-3"><strong class="mb-3">{{ $t('permissions.' + module.name) }}</strong></div>
                                    <div class="field-checkbox mb-2 mt-1" v-for="op in module.perm.operation">
                                        <Checkbox :inputId="getCheckboxInputId(module.name, op)" value="1" v-model="permission_items[module.name][op]" :binary="true" :disabled="loading || saving"/>
                                        <label :for="getCheckboxInputId(module.name, op)">
                                            {{ $t('permissions.' + op) }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <div class="" v-if="loading">
                            <ProgressSpinner style="width: 25px; height: 25px"/>
                        </div>
                        
                        <div class="text-right">
                            <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>