<script>
    import { ref } from 'vue'
    import { setMetaTitle } from '@/utils/helper'
    
    export default {
        setup() {
            setMetaTitle('meta.title.signup_confirmed');
        }
    }
</script>

<template>
    <div class="text-center">
        {{ $t('register.registering_confirmed') }}
        
        <div class="mt-4">
            <router-link :to="{name: 'signin'}">
                {{ $t('register.signin') }}
            </router-link>
        </div>
    </div>
</template>