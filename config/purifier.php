<?php

return [
    'encoding'         => 'UTF-8',
    'finalize'         => true,
    'ignoreNonStrings' => false,
    'cachePath'        => storage_path('app/purifier'),
    'cacheFileMode'    => 0755,
    'settings' => [
        'default' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'div,b,strong,i,em,u,s,a[href|title|target],ul,ol,li,p[style],br,span[style],img[width|height|alt|src],blockquote,pre,code,h1,h2,h3,h4,h5,h6,table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => true,
            'URI.DisableExternalResources' => true,
            'URI.DisableExternal'      => true,
            'URI.Munge'                => null,
            'Filter.ExtractStyleBlocks' => false,
            'HTML.Nofollow'            => false,
            'HTML.TargetBlank'         => false,
        ],
    ],
];
