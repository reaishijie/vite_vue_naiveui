<script setup lang="ts" name="Home页面">
import { onMounted, ref, h } from 'vue'
import { useUserStore } from '../store/user'
import { useRouter } from 'vue-router'
import { useMessage, NIcon } from 'naive-ui'
import { PieChartOutline, AddCircleOutline, BookmarkOutline, DocumentTextOutline, PersonOutline } from '@vicons/ionicons5'
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
const currentView = ref('home')
const handleMenuClick = (key: string) => {
  currentView.value = key
}

// 假设菜单选项数据
const menuOptions = [
  {
    label: '主页',
    key: 'home',
    icon: renderIcon(PieChartOutline)
  },
  {
    label: '添加记录',
    key: 'add-record',
    icon: renderIcon(AddCircleOutline)
  },
  {
    label: '日志列表',
    key: 'log-list',
    icon: renderIcon(DocumentTextOutline)
  },
  {
    label: '操作日志',
    key: 'operation-log',
    icon: renderIcon(BookmarkOutline)
  },
  {
    label: '个人中心',
    key: 'profile',
    icon: renderIcon(PersonOutline)
  },
  /*    *跳转页面写法*      */
  // {
  //   label: () =>
  //     h(
  //       'a',
  //       {
  //         href: '/login',
  //         // target: '_blank',
  //         rel: 'noopenner noreferrer'
  //       },
  //       '个人中心'
  //     ),
  //   key: 'hear-the-wind-sing',
  //   icon: renderIcon(PersonOutline)
  // },
]

const dropdownOptions = [
  {
    key: 'profile',
    label: '个人信息',
    icon: renderIcon(PersonOutline)
  },
  {
    key: 'change-password',
    label: '修改密码',
    icon: renderIcon(PersonOutline)
  },
  {
    key: 'logout',
    label: '退出登录',
    icon: renderIcon(PersonOutline)
  }
]
const handleLogout = () => {
  userStore.logout()
  message.success('退出账号成功！')
  setTimeout(() => {
    window.location.reload()
  }, 1000);
}
const handleSelect = (key: string) => {
  switch (key) {
    case 'logout':
      handleLogout()
      break
    case 'profile':
      currentView.value = 'profile'
      break
    case 'change-passwoed':
      break
  }
}
</script>

<template>
  <n-layout>
    <!-- header begin-->
    <n-layout>
      <n-card>
        <div class="header" style="display: flex; justify-content: space-between;  align-items: center;">
          <div>
              <img src="../assets/vue.svg" alt="" style="width: 25px; height: 25px;">
            用户中心
            <n-switch v-model:value="collapsed" />
          </div>
          <div>
            <n-dropdown :options="dropdownOptions" @select="handleSelect">
              <n-button type="info" quaternary size="small" :render-icon="renderIcon(PersonOutline)">
                个人中心
              </n-button>
            </n-dropdown>
          </div>
        </div>
      </n-card>
    </n-layout>
    <!-- header end-->
    <!-- sider begin-->
    <n-layout has-sider>
      <n-layout-sider bordered :collapsed="collapsed" :collapsed-width="64" :width="150" show-trigger
        @collapse="collapsed = true" @expand="collapsed = false">
        <!-- 侧边栏内容 -->
        <n-menu :collapsed="collapsed" :options="menuOptions" @update:value="handleMenuClick" />
      </n-layout-sider>
      <!-- sider end-->
      <!-- 右侧内容部分 -->
      <n-layout>
        <n-space vertical>
          <div class="content-area">
            <template v-if="currentView === 'home'">
              <h2>主页</h2>
              <h2>欢迎来到主页</h2>
              <p>这是主页内容</p>
            </template>

            <template v-else-if="currentView === 'add-record'">
              <h2>添加记录</h2>
              <p>这里是添加记录的表单</p>
            </template>

            <template v-else-if="currentView === 'log-list'">
              <h2>日志列表</h2>
              <p>这里显示日志列表内容</p>
            </template>

            <template v-else-if="currentView === 'operation-log'">
              <h2>操作日志</h2>
              <p>这里显示操作日志内容</p>
            </template>

            <template v-else-if="currentView === 'profile'">
              <h2>个人中心</h2>
              <p>这里显示个人信息内容</p>
            </template>
          </div>
        </n-space>
      </n-layout>
    </n-layout>
  </n-layout>
</template>

<style scoped>
.content-area {
  padding: 20px;
}
</style>