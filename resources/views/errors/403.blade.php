@extends('errors.layout')

@section('code', '403')
@section('heading', 'Access Forbidden')
@section('icon', 'shield-lock')
@section('bg', '#fdecec')
@section('color', '#dc3545')
@section('message', $exception->getMessage() ?: 'You do not have permission to open this page with your current role.')
