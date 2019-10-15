@servers(['web' => ['ddbdrift@followsearches.dandigbib.org -A -p 222']])

@task('deploy_stage', ['on' => ['web']])
    cd ~/public_html/checkouts/stage
    @if (!$branch)
      git fetch
      git checkout -f origin/develop
    @endif
    @if ($branch)
      git fetch
      git checkout -f origin/{{$branch}}
    @endif
    php -dallow_url_fopen=1 ~/bin/composer self-update
    php -dallow_url_fopen=1 ~/bin/composer install --prefer-dist --no-dev
    php ./artisan migrate
@endtask

@task('deploy_prod', ['on' => ['web']])
    cd ~/public_html/checkouts/prod
    @if (!$branch)
      git fetch
      git checkout -f origin/master
    @endif
    @if ($branch)
      git fetch
      git checkout -f origin/{{$branch}}
    @endif
    php -dallow_url_fopen=1 ~/bin/composer self-update
    php -dallow_url_fopen=1 ~/bin/composer install --prefer-dist --no-dev
    php ./artisan migrate
@endtask

