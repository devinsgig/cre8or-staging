<template>
	<header class="main-header-non-login sticky top-0 z-[998] bg-body dark:bg-dark-body">
		<Container class="px-4 py-5 lg:px-0">
			<div class="flex gap-base-2 justify-between items-center">
				<Logo :className="'max-h-8'"/>
				<div class="flex gap-base-2">
					<BaseButton type="outlined" :to="{name: 'login'}">{{$t('Login')}}</BaseButton>
					<BaseButton type="outlined" v-if="this.config.signupEnable" :to="{name: 'signup'}">{{$t('Sign up')}}</BaseButton>
				</div>
			</div>
		</Container>
	</header>
</template>

<script>
import Container from '@/components/article/Container.vue';
import Logo from '../utilities/Logo.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'
import { mapState } from 'pinia';
import { useAppStore } from '../../store/app';

export default {
	components: {
		Container, Logo, BaseButton
	},
	computed:{
		...mapState(useAppStore, ['config'])
	}
}
</script>
