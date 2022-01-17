<template>
    <div class="page-container">
        <a-tabs v-model="tab">
            <a-tab-pane tab="角色信息" key="role">
                <a-form-model ref="form" :model="form" :rules="rules" :label-col="commonLabelCol"
                              :wrapper-col="commonWrapperCol">
                    <a-row>
                        <a-col :sm="{ span: 24 }" :md="{ span: 20, offset: 2 }" :lg="{ span: 18, offset: 3 }">
                            <a-form-model-item label="角色名称" prop="name" required>
                                <a-input v-model="form.name" placeholder="请输入角色名称"></a-input>
                            </a-form-model-item>
                            <a-form-model-item label="角色描述" prop="remark">
                                <a-textarea v-model="form.remark" allow-clear placeholder="请输入角色描述"></a-textarea>
                            </a-form-model-item>
                            <a-form-model-item label="是否启用" prop="is_active">
                                <a-switch v-model="form.is_active" checked-children="是" un-checked-children="否"/>
                            </a-form-model-item>
                            <a-divider></a-divider>
                            <a-form-model-item :wrapper-col="{offset: 8}">
                                <a-button type="primary" icon="check-circle" :loading="isLoading"
                                          @click="submit">
                                    <span v-if="isLoading">保存中...</span>
                                    <span v-else>保存</span>
                                </a-button>
                            </a-form-model-item>
                        </a-col>
                    </a-row>
                </a-form-model>
            </a-tab-pane>
            <a-tab-pane tab="权限管理" key="authority">
                <a-row>
                    <a-col :sm="{ span: 24 }" :md="{ span: 20, offset: 2 }" :lg="{ span: 18, offset: 3 }">
                        <a-tree checkable ref="tree" :tree-data="permissions" v-model="rolePermissions"></a-tree>
                        <a-button type="primary" icon="check-circle" :loading="isLoading"
                                  @click="submitPermissions">
                            <span v-if="isLoading">保存中...</span>
                            <span v-else>保存</span>
                        </a-button>
                    </a-col>
                </a-row>
            </a-tab-pane>
        </a-tabs>
    </div>
</template>
<script>
export default {
    props: {
        id: {type: Number},
        type: {type: String, default: 'role'}
    },
    created: function () {
        this.tab = this.type;
        this.roleId = this.id || '';
    },
    data: function () {
        return {
            tab: '',
            roleId: '',
            isLoading: false,
            form: {
                id: '',
                name: '',
                remark: '',
                is_active: true
            },
            permissions: [],
            rolePermissions: [],
            rules: {
                name: [{required: true, message: '请输入角色名称', trigger: 'blur'}]
            }
        }
    },
    mounted: function () {
        if (this.roleId) {
            this.fetchRoleInfo();
        }
    },
    watch: {
        tab: function (val) {
            if (val === 'authority' && !this.roleId) {
                this.$message.warning('请先保存角色信息');
                var _self = this;
                setTimeout(function () {
                    _self.tab = "role";
                }, 500);
            } else if (val === 'authority') {
                this.fetchPermissions();
            }
        }
    },
    methods: {

        fetchRoleInfo: function () {
            var _self = this;
            this.$http.get(`api/manager/role/${this.roleId}`, {drawer: 'role-edit'}).then(function (res) {
                res = res.data;
                UTILS.processStatus(res.status, [STATE_CODE_SUCCESS, function () {
                    _self.$set(_self, 'form', res.result);
                }]);
            });
        },
        fetchPermissions: function () {
            var _self = this;
            this.$http.get('api/manager/role/permission', {
                params: {role_id: this.roleId},
                drawer: 'role-edit',
            }).then(function (res) {
                res = res.data;
                UTILS.processStatus(res.status, [STATE_CODE_SUCCESS, function () {
                    var permissions = _self._tidyPermissions(res.result.permissions);
                    var rolePermissions = res.result.role_permissions.map(function (item) {
                        return item.id
                    })
                    _self.$set(_self, "permissions", permissions);
                    _self.$set(_self, "rolePermissions", rolePermissions);
                }]);
            });
        },
        submit: function () {
            var _self = this;
            this.$refs.form.validate(function (valid) {
                if (!valid) return;
                _self.isLoading = true;
                _self.$http.post('api/manager/role', _self.form).then(function (res) {
                    _self.isLoading = false;
                    res = res.data;
                    UTILS.processStatus(res.status, [
                        STATE_CODE_SUCCESS, function () {
                            if (!_self.roleId) {
                                _self.roleId = res.result;
                                _self.$message.success('保存成功');
                                _self.tab = 'authority';
                            } else {
                                window.drawers['role-edit'].close();
                            }
                        },
                        STATE_CODE_PARAMETERS, function () {
                            UTILS.showMsgByFormLabel(_self, res.result);
                        }
                    ], res.result);
                }).finally(function(){
                    _self.isLoading = false;
                })
            })
        },
        submitPermissions: function () {
            if (!this.roleId) {
                this.$message.warning('请先保存角色信息');
                return;
            }
            var _self = this;
            if (!_self.rolePermissions.length) {
                this.$message.warning('请至少选择一项权限');
                return;
            }
            this.isLoading = true;
            this.$http.post('api/manager/role/permission', {
                id: this.roleId,
                permissions: _self.rolePermissions
            }).then(function (res) {
                _self.isLoading = false;
                res = res.data;
                UTILS.processStatus(res.status, [
                    STATE_CODE_SUCCESS, function () {
                        _self.$message.success('保存成功');
                    }
                ]);
            })
        },

        _tidyPermissions: function (permissions) {
            let result = permissions.filter(function (item) {
                return item.parent_id === 0;
            })

            return result.map(function (item) {
                return {
                    title: item.name,
                    key: item.id,
                    children: permissions.filter(function (child) {
                        return child.parent_id === item.id;
                    }).map(function (child) {
                        return {
                            title: child.name,
                            key: child.id,
                            children: permissions.filter(function (grandson) {
                                return grandson.parent_id === child.id;
                            }).map(function (grandson) {
                                return {
                                    title: grandson.name,
                                    key: grandson.id
                                }
                            })
                        }
                    })
                }
            })
        }
    }
}
</script>
