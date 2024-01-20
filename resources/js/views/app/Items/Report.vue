<script>
    import { setMetaTitle } from '@/utils/helper'
    import { appStore } from '@/store.js'
    
    import Chart from 'primevue/chart';
    import TabMenu from '@/views/app/Items/_TabMenu.vue'
    import ReportService from '@/service/ReportService'
    import moment from 'moment'
    
    export default {
        components: { TabMenu, Chart },
        props: {
            item: { type: Object },
        },
        watch: {
            years() {
                this.setPeriod()
            }
        },

        setup() {
            setMetaTitle('meta.title.items_report')
            
            const reportService = new ReportService()
            
            return {
                reportService,
            }
        },
        data() {
            return {
                loading: true,
                period: moment().year(),
                years: [],
                allowedPeriods: [],
                chartData: {
                },
                chartOptions: {
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        },
                    }
                }
            }
        },
        beforeMount() {
            this.setPeriod()
            this.getReport()
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
                    items.push({'label' : this.$t('items.history'), disabled : true })
                }
                    
                return items
            },
            
            setPeriod() {
                let period = [
                    { "id" : "last_year", "name" : this.$t("items.last_12_months") }
                ];
                if (!this.years.length) {
                    period.push({ "id" : moment().year(), "name" : moment().year() });
                }
                else
                {
                    this.years.forEach((year) => {
                        period.push({ "id" : year, "name" : year });
                    });
                }
                this.allowedPeriods = period
            },
            
            changePeriod(e) {
                this.period = e.value
                this.getReport()
            },
            
            getReport() {
                const search = {
                    item_id : this.$route.params.itemId,
                    status_arr : ['archive', 'termination'],
                };
                this.reportService.itemReport(this.$route.params.itemId, this.period)
                    .then(
                        (response) => {
                            let labels = response.data.labels;
                            
                            this.chartData.datasets = [
                                {
                                    label: response.data.charge.name,
                                    data: response.data.charge.values,
                                    backgroundColor: '#fcb3b3',
                                },
                                {
                                    label: response.data.balance.name,
                                    data: response.data.balance.values,
                                    backgroundColor: '#3282ba',
                                },
                                {
                                    label: response.data.deposit.name,
                                    data: response.data.deposit.values,
                                    backgroundColor: '#79c985',
                                },
                            ]
                            
                            this.chartData.labels = labels
                            
                            this.years = response.data.years
                            this.loading = false
                        },
                        (errors) => {
                        }
                    )
            },
        },
    }
</script>

<template>
    <Breadcrumb :model="getBreadcrumbs()"/>
    
    <div class="grid mt-1">
        <div class="col col-12">
            <div class="card">
                <TabMenu active="TabMenu" :item="item" :showEditButton="false" activeIndex="report" class="mb-5"/>
                
                <div class="text-right">
                    <Dropdown v-model="period" :showClear="false" :options="allowedPeriods" optionLabel="name" optionValue="id" :placeholder="$t('items.select_period')" :disabled="loading" @change="changePeriod"/>
                </div>
                
                <Chart type="bar" :data="chartData" :options="chartOptions" :loading="true"/>
            </div>
        </div>
    </div>
</template>