<?php

namespace Exdeliver\ExdeliverWcag\WcagAnalysis;

interface WcagAnalyzerInterface
{
    public function analyze(string $pageSource): array;
}