@extends('errors.layout')
@section('title', 'Request error')
@section('code', $exception->getStatusCode() ?? '4xx')
@section('desc', "We couldn't process that request.")
