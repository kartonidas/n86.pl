<script>
    import { appStore } from '@/store.js'
    import { getResponseErrors, setMetaTitle } from '@/utils/helper'
    
    import TabMenu from './../_TabMenu.vue'
    import BillForm from './_Form.vue'
    import ItemService from '@/service/ItemService'
    
    export default {
        components: { BillForm, TabMenu },
        props: {
            item: { type: Object },
        },
        setup() {
            setMetaTitle('meta.title.items_new_bill')
            
            const itemService = new ItemService()
            return {
                itemService,
            }
        },
        data() {
            return {
                errors: [],
                bill : {
                    charge_current_tenant: 1
                },
                saving: false,
            }
        },
        methods: {
            getBreadcrumbs() {
                let items = [
                    {'label' : this.$t('menu.estates'), disabled : true },
                    {'label' : this.$t('menu.estate_list'), route : { name : 'items'} },
                ]
                
                if(this.item.name != undefined)
                {
                    items.push({'label' : this.item.name, route : { name : 'item_show'} })
                    items.push({'label' : this.$t('items.bills'), route : { name : 'item_bills'} })
                    items.push({'label' : this.$t('items.new_bill'), disabled : true })
                }
                    
                return items
            },
            
            async createBill(bill) {
                this.saving = true
                this.errors = []
                
                this.itemService.createBill(this.$route.params.itemId, bill)
                    .then(
                        (response) => {
                            appStore().setToastMessage({
                                severity : 'success',
                                summary : this.$t('app.success'),
                                detail : this.$t('items.bill_added'),
                            });
                            
                            this.$router.push({name: 'item_bill_show', params: { itemId : this.$route.params.itemId, billId : response.data }})
                        },
                        (response) => {
                            this.$toast.add({ severity: 'error', summary: this.$t('app.form_error_title'), detail: this.$t('app.form_error_message'), life: 3000 });
                            this.errors = getResponseErrors(response)
                            this.saving = false
                        }
                    )
            }
        }
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <Message severity="error">
        KONCEPCJA JEST TAKA:<br/>
        Trzeba dodać stronę pomocy, gdzie będzie wyjaśnione w jaki sposób dodwać koszty.<br/>
        Niezaznaczenie "Obciąż aktualnego najemce" oznacza mniej więcej coś takiego:
        mamy czynsz najmu (np 2000PLN), w sklad tego czynszu wchodzi opłata do spółdzielni/wspólnoty (500PLN) plus opłata
        za wywóz śmieci (100PLN), obie te opłaty dodajemny bez zaznaczenia "Obciąż aktualnego najemce" dzięki temu
        w statystykach wiemy że zarobiliśmy 2000PLN-600=1400PLN (bo obie opłaty są w czynszu więc nie obciązają najemcy).
        <br/>
        Generalnie trzeba będzie ładne do opisac w pomocy, bo za skomplikowane to będzie żeby tak od strzała w tym się poruszać
    </Message>

   
    
    
    <div class="grid mt-1">
        <div class="col-12">
            <div class="card">
                <TabMenu activeIndex="fees:bills" :item="item" class="mb-5" :showEditButton="false" :showDivider="true"/>
                <BillForm @submit-form="createBill" :bill="bill" :saving="saving" :errors="errors" />
            </div>
        </div>
    </div>
</template>