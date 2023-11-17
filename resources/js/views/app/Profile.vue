<script>
    import { ref } from 'vue'
    import Breadcrumb from 'primevue/breadcrumb';
    import { useI18n } from 'vue-i18n'
    
    import Button from 'primevue/button';
    import UserService from '@/service/UserService'
    
    export default {
        setup() {
            const userService = new UserService()
            const { t } = useI18n();
            return {
                t,
                userService
            }
        },
        data() {
            return {
                loading: false,
                items: [
                    {
                        'label' : this.t('app.profile')
                    }
                ],
                home: {
                    'label' : this.t('app.home'),
                    'url' : '/'
                }
            }
        },
        methods: {
            check() {
                this.userService.isLogin().then(() => {}, () => {});
            }
        }
    }
</script>

<template>
    <Breadcrumb :home="home" :model="items" />
    
    <div class="grid mt-1">
        <div class="col">
            <div class="card p-fluid">
                <h5>{{ $t('app.profile') }}</h5>
                
                <Button label="LOGIN" @click="check"></Button>
            </div>
        </div>
    </div>
</template>