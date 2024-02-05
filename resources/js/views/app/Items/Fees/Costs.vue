<script>
    import { appStore } from '@/store.js'
    import { useVuelidate } from '@vuelidate/core'
    import { required, } from '@/utils/i18n-validators'
    import { getResponseErrors, setMetaTitle, hasAccess } from '@/utils/helper'
    
    import TabMenu from './../_TabMenu.vue'
    import ItemService from '@/service/ItemService'
    import moment from 'moment'
    
    export default {
        components: { TabMenu },
        props: {
            item: { type: Object },
        },
        setup() {
            setMetaTitle('meta.title.items_cyclical_fee_costs')
            
            const itemService = new ItemService()
            return {
                itemService,
                hasAccess,
                v: useVuelidate(),
            }
        },
        data() {
            return {
                errors: [],
                history: [],
                saving: false,
                displayConfirmation: false,
                addCostModalVisible: false,
                deleteCostId: null,
                cost: {
                    from_time: new Date()
                },
                meta: {
                    currentPage: 1,
                    perPage: this.rowsPerPage,
                    loading: false,
                    totalRecords: null,
                    totalPages: null,
                },
            }
        },
        beforeMount() {
            this.getHistory()
            
            this.itemService.getCyclicalFee(this.$route.params.itemId, this.$route.params.feeId)
                .then(
                    (response) => {
                        this.cost.cost = response.data.cost
                    }
                )
        },
        methods: {
            getHistory() {
                this.itemService.cyclicalFeeCosts(this.$route.params.itemId, this.$route.params.feeId, this.meta.perPage, this.meta.currentPage)
                    .then(
                        (response) => {
                            this.history = response.data.data
                            this.meta.totalRecords = response.data.total_rows
                            this.meta.totalPages = response.data.total_pages
                            this.meta.loading = false
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    );
            },
            
            changePage(event) {
                this.meta.currentPage = event["page"] + 1;
                this.getHistory()
            },
            
            openConfirmation(id) {
                this.displayConfirmation = true
                this.deleteCostId = id
            },
            
            confirmDeleteCyclicalFee() {
                this.itemService.removeCyclicalFeeCost(this.$route.params.itemId, this.$route.params.feeId, this.deleteCostId)
                    .then(
                        (response) => {
                            this.getHistory()
                            this.$toast.add({ severity: 'success', summary: this.$t('app.success'), detail: this.$t('items.cyclical_fee_deleted'), life: 3000 });
                        },
                        (errors) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.error'), detail: errors.response.data.message, life: 3000 });
                        }
                    )
                
                this.displayConfirmation = false
                this.deleteCostId = null
            },
            
            closeConfirmation() {
                this.displayConfirmation = false
            },
            
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                ]
                
                if(this.item.name != undefined)
                {
                    items.push({'label' : this.item.name, route : { name : 'item_show'} })
                    items.push({'label' : this.$t('items.cyclical_fees'), route : { name : 'item_fees'} })
                    items.push({'label' : this.$t('items.items_cyclical_fee_cost'), route : { name : 'item_cyclical_fee_edit'} })
                    items.push({'label' : this.$t('items.items_cyclical_fee_costs'), disabled : true })
                }
                    
                return items
            },
            
            addNewCost() {
                this.addCostModalVisible = true
                this.saving = false
            },
            
            closeAddCostModal() {
                this.addCostModalVisible = false
            },
            
            async addCost() {
                const result = await this.v.$validate()
                if (result) {
                    this.saving = true
                    
                    let cost = Object.assign({}, this.cost);
                    if(cost.from_time && cost.from_time instanceof Date)
                        cost.from_time = moment(cost.from_time).format("YYYY-MM-DD")
                    
                    this.itemService.addCyclicalFeeCost(this.$route.params.itemId, this.$route.params.feeId, cost)
                        .then(
                            (response) => {
                                this.saving = false
                                this.getHistory()
                                this.addCostModalVisible = false
                            },
                            (errors) => {
                                this.saving = false
                                this.errors = getResponseErrors(errors)
                            },
                        )
                }
            },
            
            back() {
                this.$router.push({name: 'item_cyclical_fee_edit'})
            }
        },
        validations () {
            return {
                cost: {
                    from_time: { required },
                    cost: { required },
                }
            }
        },
    }
</script>

<template>
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <TabMenu activeIndex="fees:const" :item="item" class="mb-3" :showEditButton="false" :showDivider="true"/>
                
                <div class="mb-3 flex justify-content-between w-full">
                    <Button type="button" :label="$t('app.back')" iconPos="left" icon="pi pi-angle-left" @click="back" class="p-button-secondary w-auto text-center"></Button>
                    <Button type="button" icon="pi pi-plus" @click="addNewCost" class="p-button-primary" v-tooltip.left="$t('items.items_cyclical_fee_add_cost')"></Button>
                </div>
                
                <DataTable :value="history" stripedRows class="p-datatable-gridlines" :totalRecords="meta.totalRecords" :rowHover="true" :lazy="true" :paginator="true" :pageCount="meta.totalPages" :rows="meta.perPage" @page="changePage" :loading="meta.loading">
                    <Column :header="$t('items.from_day')" style="width: 180px;" field="from_time"></Column>
                    <Column :header="$t('items.value')" field="cost" class="text-right">
                        <template #body="{ data }">
                            {{ numeralFormat(data.cost, '0.00') }} {{ data.currency }}
                        </template>
                    </Column>
                    <Column field="delete" v-if="hasAccess('item:update')" style="min-width: 60px; width: 60px" class="text-center">
                        <template #body="{ data }">
                            <Button :disabled="!data.can_delete" icon="pi pi-trash" v-tooltip.bottom="$t('app.remove')" class="p-button-danger p-2" style="width: auto" @click="openConfirmation(data.id)"/>
                        </template>
                    </Column>
                    <template #empty>
                        {{ $t('items.empty_cyclical_fee_history_cost') }}
                    </template>
                </DataTable>
                <Dialog :header="$t('app.confirmation')" v-model:visible="displayConfirmation" :style="{ width: '450px' }" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span>{{ $t('app.remove_object_confirmation') }}</span>
                    </div>
                    <template #footer>
                        <Button :label="$t('app.no')" icon="pi pi-times" @click="closeConfirmation" class="p-button-text" />
                        <Button :label="$t('app.yes')" icon="pi pi-check" @click="confirmDeleteCyclicalFee" class="p-button-danger" autofocus />
                    </template>
                </Dialog>
                
                <Dialog v-model:visible="addCostModalVisible" modal :header="$t('items.cyclical_fee_add_cost')" style="{ width: '50rem' }" :breakpoints="{ '1499px': '50vw', '999px': '95vw' }">
                    <form v-on:submit.prevent="submitForm" class="sticky-footer-form">
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
                                    <div class="field col-12 md:col-6 mb-4">
                                        <label for="document_date" v-required class="block text-900 font-medium mb-2">{{ $t('items.from_time') }}</label>
                                        <Calendar id="document_date" v-model="cost.from_time" :class="{'p-invalid' : v.cost.from_time.$error}" :placeholder="$t('items.from_time')" showIcon :disabled="saving"/>
                                        <div v-if="v.cost.from_time.$error">
                                            <small class="p-error">{{ v.cost.from_time.$errors[0].$message }}</small>
                                        </div>
                                    </div>
                                    <div class="field col-12 md:col-6 mb-4">
                                        <label for="cost" v-required class="block text-900 font-medium mb-2">{{ $t('items.cost') }}</label>
                                        <InputNumber id="cost" :useGrouping="false" locale="pl-PL" :minFractionDigits="2" :maxFractionDigits="2" :placeholder="$t('items.cost')" class="w-full" :class="{'p-invalid' : v.cost.cost.$error}" v-model="cost.cost" :disabled="saving"/>
                                        <div v-if="v.cost.cost.$error">
                                            <small class="p-error">{{ v.cost.cost.$errors[0].$message }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-content-between align-items-center">
                            <Button type="button" :label="$t('app.cancel')" @click="closeAddCostModal" class="p-button-secondary w-auto text-center"></Button>
                            <Button type="submit" :label="$t('app.save')" :disabled="saving" @click="addCost" iconPos="right" icon="pi pi-save" class="w-auto text-center"></Button>
                        </div>
                    </form>
                </Dialog>
            </div>
        </div>
    </div>
</template>