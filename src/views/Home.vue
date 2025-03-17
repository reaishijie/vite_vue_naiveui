<script setup lang="ts" name="Home页面">
import { onMounted, ref, h } from 'vue'
import { useUserStore } from '../store/user'
import { useRouter } from 'vue-router'
import { useMessage, NIcon } from 'naive-ui'
import { HomeOutline } from '@vicons/ionicons5'
const message = useMessage()
const router = useRouter()
const userStore = useUserStore()

onMounted(() => {
  if (!userStore.logState) {
    message.error('请先登录账号')
    router.push('/login')
  }
})

// 控制侧边栏折叠的状态
const collapsed = ref(false)

// renderIcon 函数：动态渲染图标
function renderIcon(icon: any) {
  return () => h(NIcon, { size: 20 }, { default: () => h(icon) })
}

// 假设菜单选项数据
const menuOptions = [
  {
    label: () =>
      h(
        'a',
        {
          href: '/login',
          // target: '_blank',
          rel: 'noopenner noreferrer'
        },
        '主页'
      ),
    key: 'hear-the-wind-sing',
    icon: renderIcon(HomeOutline)
  },
  {
    label: '关于我们',
    key: 'about',
    icon: renderIcon(HomeOutline)
  },
  {
    label: '联系我们',
    key: 'contact',
    icon: renderIcon(HomeOutline)

  }
]

console.log(menuOptions);

</script>

<template>
  <n-layout>
    <!-- header -->
    <n-layout>
      <n-card>
        <!-- <n-icon id="HomeOutline" size="20">
          <HomeOutline />
        </n-icon>用户中心 -->
        <!-- 控制侧边栏折叠的开关 -->
      <n-icon> <img src="../assets/vue.svg" alt="用户中心" size="20"></n-icon>用户中心
        <n-switch v-model:value="collapsed" />

        <n-button type="error" >退出登录</n-button>
      </n-card>
    </n-layout>

    <n-layout has-sider>
      <!-- 侧边栏 -->

      <n-layout-sider bordered :collapsed="collapsed" :collapsed-width="64" :width="150" show-trigger
        @collapse="collapsed = true" @expand="collapsed = false">
        <!-- 侧边栏内容 -->
        <n-menu :collapsed="collapsed" :options="menuOptions" />
      </n-layout-sider>

      <!-- 右侧内容部分 -->
      <n-layout>
        <n-space vertical>
          <!-- 右侧内容 -->
          <div v-if="!collapsed">
            <p>这是右侧展开时的内容。</p>
          </div>
          <div v-else>
            <p>这是右侧折叠时的内容。</p>
            <h3>demo</h3>
          </div>
        </n-space>
      </n-layout>
    </n-layout>
  </n-layout>
</template>

<style scoped>
</style>