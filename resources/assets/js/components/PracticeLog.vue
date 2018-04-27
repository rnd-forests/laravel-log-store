<template>
    <el-row :gutter="10">
        <el-col :span="12">
            <el-card>
                <el-form ref="form" label-position="left" label-width="100px">
                    <el-form-item label="Log Type" :error="form.errors.get('type') || null">
                        <el-input v-model="form.type"></el-input>
                    </el-form-item>

                    <el-form-item label="Lesson ID" :error="form.errors.get('lesson_id') || null">
                        <el-input v-model="form.lesson_id" :clearable="true"></el-input>
                    </el-form-item>

                    <el-form-item label="Start Time" :error="form.errors.get('start') || null">
                        <el-date-picker
                            v-model="form.start"
                            type="datetime"
                            placeholder="Please select the start time"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            :picker-options="datePickerOptions">
                        </el-date-picker>
                    </el-form-item>

                    <el-form-item label="End Time" :error="form.errors.get('end') || null">
                        <el-date-picker
                            v-model="form.end"
                            type="datetime"
                            placeholder="Please select the end time"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            :picker-options="datePickerOptions">
                        </el-date-picker>
                    </el-form-item>

                    <el-form-item label="Score" :error="form.errors.get('score') || null">
                        <el-slider
                            v-model="form.score"
                            :step="10"
                            :min="0"
                            :max="100"
                            show-stops>
                        </el-slider>
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="onSubmit" round>Store Log Instance</el-button>
                    </el-form-item>
                </el-form>
            </el-card>
        </el-col>

        <el-col :span="12">
            <template v-if="logs.length">
                <el-card v-for="log in logs" :key="log._id" class="mb-2">
                    <pre>{{ log }}</pre>
                </el-card>
            </template>
            <el-card v-else>
                No practice log instance available.
            </el-card>
        </el-col>
    </el-row>
</template>

<script>
    import _ from 'lodash';
    import Form from '../utils/Form';
    import axios from '../utils/axios'

    export default {
        data () {
            return {
                form: new Form({
                    'type': 'practice',
                    'user_id': App.user.id,
                    'lesson_id': '',
                    'start': '',
                    'end': '',
                    'score': 0
                }),

                logs: [],

                datePickerOptions: {
                    shortcuts: [{
                        text: 'Today',
                        onClick(picker) {
                            picker.$emit('pick', new Date());
                        }
                    }, {
                        text: 'Yesterday',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24);
                            picker.$emit('pick', date);
                        }
                    }]
                }
            };
        },

        computed: {
            payload () {
                return {
                    'type': this.form.type,
                    'data': JSON.stringify(_.pick(this.form, ['user_id', 'lesson_id', 'start', 'end', 'score']))
                };
            }
        },

        methods: {
            fetchPracticeLogs () {
                axios.get('/api/logs/practice')
                    .then(response => this.logs = response.data)
                    .catch(error => this.$message.error(error.message));
            },

            onSubmit () {
                this.form.post('/api/logs', this.payload)
                    .then(data => {
                        this.$message({type: 'success', 'message': data.message});
                        this.fetchPracticeLogs();
                    }).catch(error => this.$message.error(error.message));
            }
        },

        created () {
            this.fetchPracticeLogs();
        }
    }
</script>

<style>
    .el-date-editor.el-input, .el-date-editor.el-input__inner {
        width: 100%;
    }
</style>
