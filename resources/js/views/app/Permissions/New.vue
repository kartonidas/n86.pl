<script>
    import { ref } from 'vue'
    import { useI18n } from 'vue-i18n'
    import { useRouter } from 'vue-router'
    import { useVuelidate } from '@vuelidate/core'
    import { required } from '@/utils/i18n-validators'
    import { getResponseErrors, hasAccess } from '@/utils/helper'
    
    import { appStore } from '@/store.js'
    import PermissionService from '@/service/PermissionService'
    
    export default {
        setup() {
            const router = useRouter()
            const permissionService = new PermissionService()
            const { t } = useI18n();
            
            return {
                t,
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
                        {'label' : this.t('menu.users'), disabled : true },
                        {'label' : this.t('menu.permissions'), route : { name : 'permissions'} },
                        {'label' : this.t('permissions.new'), route : { name : 'permissions'}, disabled : true },
                    ],
                }
            }
        },
        mounted() {
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
                                    summary : this.t('app.success'),
                                    detail : this.t('permissions.added'),
                                });
                                
                                if(hasAccess('permission:update'))
                                    this.router.push({name: 'permission_edit', params: { permissionId : response.data }})
                                else
                                    this.router.push({name: 'permissions'})
                            },
                            (response) => {
                                this.errors = getResponseErrors(response)
                                this.saving = false
                            }
                        )
                }
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
            <div class="card p-fluid">
                <form v-on:submit.prevent="createGroup">
                    <div class="mb-4">
                        <div class="p-fluid">
                            <div class="formgrid grid">
                                <div class="field col-12">
                                    <label for="name" class="block text-900 font-medium mb-2">{{ $t('permissions.name') }}</label>
                                    <InputText id="name" type="text" :placeholder="$t('permissions.name')" class="w-full" :class="{'p-invalid' : v$.permission.name.$error}" v-model="permission.name" :disabled="loading || saving"/>
                                    <div v-if="v$.permission.name.$error">
                                        <small class="p-error">{{ v$.permission.name.$errors[0].$message }}</small>
                                    </div>
                                </div>
                                
                                <div class="field col-12">
                                    <div class="field-checkbox mb-0">
                                        <Checkbox inputId="defaultCheck" name="is_default" value="1" v-model="permission.is_default" :binary="true" :disabled="loading || saving"/>
                                        <label for="defaultCheck">{{ $t('permissions.default') }}</label>
                                    </div>
                                </div>
                                
                                <div v-for="module in modules" class="field col-12 sm:col-6 md:col-3">
                                    <div class="mb-2"><strong class="mb-3">{{ $t('permissions.' + module.name) }}</strong></div>
                                    <div class="field-checkbox mb-1 mt-1" v-for="op in module.perm.operation">
                                        <Checkbox :inputId="getCheckboxInputId(module.name, op)" value="1" v-model="permission_items[module.name][op]" :binary="true" :disabled="loading || saving"/>
                                        <label :for="getCheckboxInputId(module.name, op)">
                                            {{ $t('permissions.' + op) }}
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
                
                    <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" class="w-auto text-center"></Button>
                </form>
            </div>
        </div>
    </div>
</template>