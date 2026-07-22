@extends('errors.layout')
@section('title', 'Something went wrong')
@section('code', $exception->getStatusCode() ?? '5xx')
@section('desc', "An unexpected error occurred on our end. We've been notified — please try again shortly.")
