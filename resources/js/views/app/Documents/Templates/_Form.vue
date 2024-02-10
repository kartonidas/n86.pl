<script>
    import { ref, reactive, computed } from 'vue'
    import { useVuelidate } from '@vuelidate/core'
    import { required, helpers, maxLength } from '@/utils/i18n-validators'
    import { getValues } from '@/utils/helper'
    import Editor from 'primevue/editor';
    import EditorToolbar from './../../_partials/EditorToolbar.vue'
    
    import DocumentTemplateService from '@/service/DocumentTemplateService'
    
    export default {
        components: { Editor, EditorToolbar },
        emits: ['submit-form', 'back'],
        setup() {
            const documentTemplateService = new DocumentTemplateService()
            return {
                documentTemplateService,
            }
        },
        watch: {
            template() {
                this.toValidate = this.template
            }
        },
        data() {
            const toValidate = ref(this.template)
            const state = reactive({ 'template' : toValidate })
            const rules = computed(() => {
                const rules = {
                    template: {
                        title: { required, maxLengthValue: maxLength(200) },
                        type: { required },
                        content: { required },
                    }
                }
                
                return rules
            })
            
            return {
                template_types: getValues('documents.types'),
                v: useVuelidate(rules, state),
                toValidate: toValidate,
                variables: getValues("templates.variables")
            }
        },
        props: {
            template: { type: Object },
            saving: { type: Boolean },
            loading: { type: Boolean },
            errors: { type: Array },
            source: { type: String, default: 'new' },
        },
        methods: {
            async submitForm() {
                const result = await this.v.$validate()
                if (result)
                    this.$emit('submit-form')
                else
                    this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
            },
            
            back() {
                this.$router.push({name: 'documents_templates_show', params: { templateId : this.template.id }})
            }
        }
    };
</script>

<template>
    <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
        <Message severity="error" :closable="false" v-if="errors.length" class="mb-5">
            <ul class="list-unstyled">
                <li v-for="error of errors">
                    {{ error }}
                </li>
            </ul>
        </Message>
        <div>
            <div class="p-fluid">
                <div class="formgrid grid">
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="title" v-required class="block text-900 font-medium mb-2">{{ $t('documents.template_title') }}</label>
                        <InputText id="title" type="text" :placeholder="$t('documents.template_title')" class="w-full" :class="{'p-invalid' : v.template.title.$error}" v-model="template.title" :disabled="saving || loading"/>
                        <div v-if="v.template.title.$error">
                            <small class="p-error">{{ v.template.title.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 md:col-6 mb-4">
                        <label for="type" v-required class="block text-900 font-medium mb-2">{{ $t('documents.template_type') }}</label>
                        <Dropdown id="type" v-model="template.type" :options="template_types" :class="{'p-invalid' : v.template.type.$error}" optionLabel="name" optionValue="id" :placeholder="$t('documents.template_type')" class="w-full" :disabled="saving || loading"/>
                        <div v-if="v.template.type.$error">
                            <small class="p-error">{{ v.template.type.$errors[0].$message }}</small>
                        </div>
                    </div>
                    
                    <div class="field col-12 mb-4">
                        <label for="content" v-required class="block text-900 font-medium mb-2">{{ $t('documents.template_content') }}</label>
                        <Editor v-model="template.content" editorStyle="height: 320px">
                            <template v-slot:toolbar>
                                <EditorToolbar/>
                            </template>
                        </Editor>
                        <div v-if="v.template.content.$error">
                            <small class="p-error">{{ v.template.content.$errors[0].$message }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            {{ $t('documents.allowed_variables') }}:
            <ul>
                <li v-for="value in variables">
                    {{ value.var }} - {{ value.label }}
                </li>
            </ul>
        </div>
        
        <div class="form-footer">
            <div v-if="source == 'new'">
                <div class="text-right">
                    <Button type="submit" :label="$t('app.save')" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
            <div v-else>
                <div v-if="loading">
                    <ProgressSpinner style="width: 25px; height: 25px"/>
                </div>
                
                <div class="flex justify-content-between align-items-center">
                    <Button type="button" :label="$t('app.cancel')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="submit" :label="$t('app.save')" v-if="!loading" :loading="saving" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                </div>
            </div>
        </div>
    </form>
</template>