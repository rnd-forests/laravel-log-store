@extends('layouts.app')

@section('content')
    <el-container>
        <el-main>
            <el-row :gutter="20">
                <el-col :span="14" :offset="5">
                    <practice-log></practice-log>
                </el-col>
            </el-row>
        </el-main>
    </el-container>
@endsection
