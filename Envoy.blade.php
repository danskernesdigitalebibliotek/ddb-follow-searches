@servers(['web' => ['ddbdrift@followsearches.dandigbib.org -A -p 222']])

@setup
  $dir = '~/public_html/checkouts/stage';

  if (!$branch) {
    $branch = 'develop';
  }

  if ($env === 'prod') {
    $dir = '~/public_html/checkouts/prod';
    $branch = 'master';
  }
@endsetup

@task('deploy', ['on' => ['web'], 'confirm' => true])
  cd {{ $dir }}
  git fetch
  git reset --hard origin/{{ $branch }}

  php -dallow_url_fopen=1 ~/bin/composer self-update
  php -dallow_url_fopen=1 ~/bin/composer install --prefer-dist --no-dev
  php ./artisan migrate --force
@endtask
