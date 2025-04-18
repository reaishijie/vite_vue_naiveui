<script setup lang="ts" name="Home页面">
import { onMounted, ref, h } from 'vue'
import { useUserStore } from '../store/user'
import { useRouter } from 'vue-router'
import { useMessage, NIcon, NCard, NButton, NLayout, NMenu, NSpace, NDropdown, NLayoutHeader, NLayoutSider  } from 'naive-ui'
import { PieChartOutline, AddCircleOutline, BookmarkOutline, DocumentTextOutline, PersonOutline, KeyOutline, LogOutOutline, MedicalOutline, ListOutline, Reload } from '@vicons/ionicons5'
import Log from '../components/Log.vue'
import LogList from '../components/LogList.vue'
import AddLog from '../components/AddLog.vue'
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
const changeCollapsed = () => {
  collapsed.value = !collapsed.value
}

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
    label: '控制台',
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
    icon: renderIcon(MedicalOutline)
  },
  {
    key: 'change-password',
    label: '修改密码',
    icon: renderIcon(KeyOutline)
  },
  {
    key: 'logout',
    label: '退出登录',
    icon: renderIcon(LogOutOutline)
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
const handleRefresh = () => {
  window.location.reload()
}

//handsome  同款
console.log("\n %c 存在网 " + " Pro %c cunzai.net", "color:#fff;background:linear-gradient(90deg,#448bff,#44e9ff);padding:5px 0;", "color:#000;background:linear-gradient(90deg,#44e9ff,#ffffff);padding:5px 10px 5px 0px;");

</script>

<template>
  <!-- sider begin-->
  <n-layout has-sider>
    <n-layout-sider bordered :collapsed="collapsed" :collapsed-width="64" :width="150" show-trigger
      collapse-mode="width" @collapse="collapsed = true" @expand="collapsed = false">
      <div style="display: flex; text-align: center;">
        <img src="../assets/vue.svg" alt="" style="display: flex; margin: 10px;">
        <span v-if="!collapsed" style="display: flex; margin-left: 10px; margin-top: 20px;">用户中心</span>
      </div>
      <!-- 侧边栏内容 -->
      <n-menu :collapsed="collapsed" :collapsed-icon-size="22" :options="menuOptions" @update:value="handleMenuClick" />
    </n-layout-sider>
    <!-- sider end-->
    <!-- header begin-->
    <n-layout>
      <n-layout-header>
        <n-card>
          <div class="header" style="display: flex; justify-content: space-between;  align-items: center;">
            <div>
              <n-icon @click="changeCollapsed" style="cursor: pointer;" size="25" >
                <ListOutline />
              </n-icon>
              <n-icon @click="handleRefresh" style="cursor: pointer;" size="20" id="reload">
                <Reload />
              </n-icon>
            </div>
            <div>
              <n-dropdown :options="dropdownOptions" @select="handleSelect">
                <n-button type="info" quaternary size="small">
                  <img
                    :src="'http://q.qlogo.cn/headimg_dl?dst_uin=' + (userStore.storeUserInfo?.username && !isNaN(Number(userStore.storeUserInfo.username)) ? userStore.storeUserInfo.username : '2900383833') + '&spec=640&img_type=svg'"
                    alt="touxiang" style="display: flex; width: 20px; padding-right: 10px;">
                  {{ userStore.storeUserInfo?.username }}
                </n-button>
              </n-dropdown>
            </div>
          </div>
        </n-card>
      </n-layout-header>
      <!-- header end-->
      <!-- 右侧内容部分 -->
      <n-layout>
        <n-space vertical>
          <div class="content-area">
            <template v-if="currentView === 'home'">
              <h2>主页</h2>
              <div style="display: flex; justify-content: center; align-items: center;">
                <div style="display: flex;margin: 20px;">
                  <n-card>1

                  </n-card>
                </div>
                <div>
                  <n-card>2

                  </n-card>
                </div>
              </div>
            </template>

            <template v-else-if="currentView === 'add-record'">
              <AddLog />
            </template>

            <template v-else-if="currentView === 'log-list'">
              <LogList></LogList>
            </template>

            <template v-else-if="currentView === 'operation-log'">
              <h2>操作日志</h2>
              <p>这里显示操作日志内容</p>
              <Log />
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
#reload {
  margin-left: 20px;
}
</style>