#!/bin/bash
if [[ $1 == "--help" ]]; then
  echo "Supported parameters:"
  echo "--rights             Reset file permissions"
  echo "--cc                 Clear model cache"
  echo "--no-restart         Do not restart services at the end of the update"
  echo "--no-system-files    Do not copy files from system folder like nginx or php-fpm configs"

  exit 0
fi

INIFILE=/opt/openitc/etc/mysql/mysql.cnf
DUMPINIFILE=/opt/openitc/etc/mysql/dump.cnf
BASHCONF=/opt/openitc/etc/mysql/bash.conf

if [[ ! -f "$BASHCONF" ]]; then
  MYSQL_USER=$(php -r "echo parse_ini_file('/opt/openitc/etc/mysql/mysql.cnf')['user'];")
  MYSQL_DATABASE=$(php -r "echo parse_ini_file('/opt/openitc/etc/mysql/mysql.cnf')['database'];")
  MYSQL_PASSWORD=$(awk '$1 == "password" { print }' "/opt/openitc/etc/mysql/mysql.cnf" |cut -d= -f2 | sed 's/^\s*//' | sed 's/\s*$//' | sed 's_/_\\/_g')
  MYSQL_HOST=$(php -r "echo parse_ini_file('/opt/openitc/etc/mysql/mysql.cnf')['host'];")
  MYSQL_PORT=$(php -r "echo parse_ini_file('/opt/openitc/etc/mysql/mysql.cnf')['port'];")

  echo "dbc_dbuser='${MYSQL_USER}'" >$BASHCONF
  echo "dbc_dbpass='${MYSQL_PASSWORD}'" >>$BASHCONF
  echo "dbc_dbserver='${MYSQL_HOST}'" >>$BASHCONF
  echo "dbc_dbport='${MYSQL_PORT}'" >>$BASHCONF
  echo "dbc_dbname='${MYSQL_DATABASE}'" >>$BASHCONF
fi

. /opt/openitc/etc/mysql/bash.conf

APPDIR="/opt/openitc/frontend"

echo "Create mysqldump of your current database"
BACKUP_TIMESTAMP=$(date '+%Y-%m-%d_%H-%M-%S')
BACKUP_DIR='/opt/openitc/nagios/backup'
mkdir -p $BACKUP_DIR
#If you have mysql binlog enabled uses this command:
#mysqldump --defaults-extra-file=${DUMPINIFILE} --databases $dbc_dbname --flush-privileges --single-transaction --master-data=1 --flush-logs --triggers --routines --events --hex-blob \
mysqldump --defaults-extra-file=${DUMPINIFILE} --databases $dbc_dbname --flush-privileges --single-transaction --triggers --routines --events --hex-blob \
  --ignore-table=$dbc_dbname.nagios_acknowledgements \
  --ignore-table=$dbc_dbname.nagios_commands \
  --ignore-table=$dbc_dbname.nagios_commenthistory \
  --ignore-table=$dbc_dbname.nagios_comments \
  --ignore-table=$dbc_dbname.nagios_configfiles \
  --ignore-table=$dbc_dbname.nagios_configfilevariables \
  --ignore-table=$dbc_dbname.nagios_conninfo \
  --ignore-table=$dbc_dbname.nagios_contact_addresses \
  --ignore-table=$dbc_dbname.nagios_contact_notificationcommands \
  --ignore-table=$dbc_dbname.nagios_contactgroup_members \
  --ignore-table=$dbc_dbname.nagios_contactgroups \
  --ignore-table=$dbc_dbname.nagios_contactnotificationmethods \
  --ignore-table=$dbc_dbname.nagios_contactnotifications \
  --ignore-table=$dbc_dbname.nagios_contacts \
  --ignore-table=$dbc_dbname.nagios_contactstatus \
  --ignore-table=$dbc_dbname.nagios_customvariables \
  --ignore-table=$dbc_dbname.nagios_customvariablestatus \
  --ignore-table=$dbc_dbname.nagios_dbversion \
  --ignore-table=$dbc_dbname.nagios_downtimehistory \
  --ignore-table=$dbc_dbname.nagios_eventhandlers \
  --ignore-table=$dbc_dbname.nagios_externalcommands \
  --ignore-table=$dbc_dbname.nagios_flappinghistory \
  --ignore-table=$dbc_dbname.nagios_host_contactgroups \
  --ignore-table=$dbc_dbname.nagios_host_contacts \
  --ignore-table=$dbc_dbname.nagios_host_parenthosts \
  --ignore-table=$dbc_dbname.nagios_hostchecks \
  --ignore-table=$dbc_dbname.nagios_hostdependencies \
  --ignore-table=$dbc_dbname.nagios_hostescalation_contactgroups \
  --ignore-table=$dbc_dbname.nagios_hostescalation_contacts \
  --ignore-table=$dbc_dbname.nagios_hostescalations \
  --ignore-table=$dbc_dbname.nagios_hostgroup_members \
  --ignore-table=$dbc_dbname.nagios_hostgroups \
  --ignore-table=$dbc_dbname.nagios_hosts \
  --ignore-table=$dbc_dbname.nagios_hoststatus \
  --ignore-table=$dbc_dbname.nagios_instances \
  --ignore-table=$dbc_dbname.nagios_logentries \
  --ignore-table=$dbc_dbname.nagios_notifications \
  --ignore-table=$dbc_dbname.nagios_processevents \
  --ignore-table=$dbc_dbname.nagios_programstatus \
  --ignore-table=$dbc_dbname.nagios_runtimevariables \
  --ignore-table=$dbc_dbname.nagios_scheduleddowntime \
  --ignore-table=$dbc_dbname.nagios_service_contactgroups \
  --ignore-table=$dbc_dbname.nagios_service_contacts \
  --ignore-table=$dbc_dbname.nagios_service_parentservices \
  --ignore-table=$dbc_dbname.nagios_servicechecks \
  --ignore-table=$dbc_dbname.nagios_servicedependencies \
  --ignore-table=$dbc_dbname.nagios_serviceescalation_contactgroups \
  --ignore-table=$dbc_dbname.nagios_serviceescalation_contacts \
  --ignore-table=$dbc_dbname.nagios_serviceescalations \
  --ignore-table=$dbc_dbname.nagios_servicegroup_members \
  --ignore-table=$dbc_dbname.nagios_servicegroups \
  --ignore-table=$dbc_dbname.nagios_services \
  --ignore-table=$dbc_dbname.nagios_servicestatus \
  --ignore-table=$dbc_dbname.nagios_statehistory \
  --ignore-table=$dbc_dbname.nagios_systemcommands \
  --ignore-table=$dbc_dbname.nagios_timedeventqueue \
  --ignore-table=$dbc_dbname.nagios_timedevents \
  --ignore-table=$dbc_dbname.nagios_timeperiod_timeranges \
  --ignore-table=$dbc_dbname.nagios_timeperiods \
  --ignore-table=$dbc_dbname.statusengine_dbversion \
  --ignore-table=$dbc_dbname.statusengine_host_acknowledgements \
  --ignore-table=$dbc_dbname.statusengine_host_downtimehistory \
  --ignore-table=$dbc_dbname.statusengine_host_notifications \
  --ignore-table=$dbc_dbname.statusengine_host_scheduleddowntimes \
  --ignore-table=$dbc_dbname.statusengine_host_statehistory \
  --ignore-table=$dbc_dbname.statusengine_hostchecks \
  --ignore-table=$dbc_dbname.statusengine_hoststatus \
  --ignore-table=$dbc_dbname.statusengine_logentries \
  --ignore-table=$dbc_dbname.statusengine_nodes \
  --ignore-table=$dbc_dbname.statusengine_perfdata \
  --ignore-table=$dbc_dbname.statusengine_service_acknowledgements \
  --ignore-table=$dbc_dbname.statusengine_service_downtimehistory \
  --ignore-table=$dbc_dbname.statusengine_service_notifications \
  --ignore-table=$dbc_dbname.statusengine_service_scheduleddowntimes \
  --ignore-table=$dbc_dbname.statusengine_service_statehistory \
  --ignore-table=$dbc_dbname.statusengine_servicechecks \
  --ignore-table=$dbc_dbname.statusengine_servicestatus \
  --ignore-table=$dbc_dbname.statusengine_tasks \
  --ignore-table=$dbc_dbname.statusengine_users \
  >$BACKUP_DIR/openitcockpit_dump_$BACKUP_TIMESTAMP.sql

echo "---------------------------------------------------------------"
echo "Convert MySQL Tables from utf8_general_ci to utf8mb4_general_ci..."

mysql --defaults-extra-file=${INIFILE} -e "ALTER DATABASE ${dbc_dbname} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

mysql --defaults-extra-file=${INIFILE} --batch --skip-column-names -e "SELECT TABLE_NAME FROM \`information_schema\`.\`TABLES\` WHERE \`TABLE_SCHEMA\`='${dbc_dbname}' AND \`TABLE_NAME\` NOT LIKE 'nagios_%' AND \`TABLE_COLLATION\`='utf8_general_ci'" | while read TABLE_NAME; do
    echo "ALTER TABLE \`${TABLE_NAME}\` CONVERT TO CHARACTER SET utf8mb4; ✔"
    mysql --defaults-extra-file=${INIFILE} -e "ALTER TABLE \`${TABLE_NAME}\` CONVERT TO CHARACTER SET utf8mb4;"
done

echo "Running openITCOCKPIT Core database migration"
oitc migrations migrate

oitc migrations seed

echo "Running openITCOCKPIT Module database migration/s"
for PLUGIN in $(ls -1 "${APPDIR}/plugins"); do
  if [[ "$PLUGIN" == *Module ]]; then
    if [[ -d "${APPDIR}/plugins/${PLUGIN}/config/Migrations" ]]; then
      echo "Running openITCOCKPIT ${PLUGIN} database migration"
      oitc migrations migrate -p "${PLUGIN}"
    fi

    if [[ -d "${APPDIR}/plugins/${PLUGIN}/config/Seeds" ]]; then
      num_files=$(find "${APPDIR}/plugins/${PLUGIN}/config/Seeds" -mindepth 1 -iname "*.php" -type f | wc -l)
      if [[ "$num_files" -gt 0 ]]; then
        echo "Importing default records for ${PLUGIN} into database"
        oitc migrations seed -p "${PLUGIN}"
      fi
    fi

  fi
done

if [ -d "${APPDIR}/plugins/CheckmkModule/src" ]; then
    oitc checkmkNagiosExport --init
fi

echo "---------------------------------------------------------------"
echo "Convert MySQL Tables from utf8_general_ci to utf8mb4_general_ci..."

mysql --defaults-extra-file=${INIFILE} -e "ALTER DATABASE ${dbc_dbname} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

mysql --defaults-extra-file=${INIFILE} --batch --skip-column-names -e "SELECT TABLE_NAME FROM \`information_schema\`.\`TABLES\` WHERE \`TABLE_SCHEMA\`='${dbc_dbname}' AND \`TABLE_NAME\` NOT LIKE 'nagios_%' AND \`TABLE_COLLATION\`='utf8_general_ci'" | while read TABLE_NAME; do
    echo "ALTER TABLE \`${TABLE_NAME}\` CONVERT TO CHARACTER SET utf8mb4; ✔"
    mysql --defaults-extra-file=${INIFILE} -e "ALTER TABLE \`${TABLE_NAME}\` CONVERT TO CHARACTER SET utf8mb4;"
done

echo "Update Containertype from Devicegroup to Node"
mysql "--defaults-extra-file=$INIFILE" -e "UPDATE containers SET containertype_id=5 WHERE containertype_id=4"

#Check and create missing cronjobs
#oitc api --model Cronjob --action create_missing_cronjobs --data ""

#Compress and minify javascript files
oitc compress

#Acc ALC dependencies config for itc core
echo "---------------------------------------------------------------"
echo "Scan for new user permissions. This will take a while..."
oitc Acl.acl_extras aco_sync

#Set default permissions, check for always allowed permissions and dependencies
oitc roles --enable-defaults --admin

#Check for browser push notification commands
echo "Check for browser push notification commands"
#oitc api --model Commands --action addByUuid --ignore-errors 1 --data 'host-notify-by-browser-notification' '/opt/openitc/frontend/bin/cake send_push_notification --type Host --notificationtype $NOTIFICATIONTYPE$ --hostuuid "$HOSTNAME$" --state "$HOSTSTATEID$" --output "$HOSTOUTPUT$"  --ackauthor "$NOTIFICATIONAUTHOR$" --ackcomment "$NOTIFICATIONCOMMENT$" --user-id $_CONTACTOITCUSERID$' '3' 'cd13d22e-acd4-4a67-997b-6e120e0d3153' 'Send a host notification to the browser window'
#oitc api --model Commands --action addByUuid --ignore-errors 1 --data 'service-notify-by-browser-notification' '/opt/openitc/frontend/bin/cake send_push_notification --type Service --notificationtype $NOTIFICATIONTYPE$ --hostuuid "$HOSTNAME$" --serviceuuid "$SERVICEDESC$" --state "$SERVICESTATEID$" --output "$SERVICEOUTPUT$" --ackauthor "$NOTIFICATIONAUTHOR$" --ackcomment "$NOTIFICATIONCOMMENT$" --user-id $_CONTACTOITCUSERID$' '3' 'c23255b7-5b1a-40b4-b614-17837dc376af ' 'Send a service notification to the browser window'

#Generate documentation
#oitc docu_generator
#oitc systemsettings_import

NORESTART=false
NOSYSTEMFILES=false
for i in "$@"; do
  case $i in
  --cc)
    echo "Clear out Model Cache /opt/openitc/frontend/tmp/cache/models/"
    rm -rf /opt/openitc/frontend/tmp/cache/models/*
    echo "Clear out CLI Model Cache /opt/openitc/frontend/tmp/cli/cache/cli/models/"
    rm -rf /opt/openitc/frontend/tmp/cli/cache/cli/models/*
    ;;

  --rights)
    oitc rights
    ;;

  --no-restart)
    NORESTART=true
    ;;

    --no-system-files)
    NOSYSTEMFILES=true
    ;;

  *)
    #No default at the moment
    ;;
  esac
done

echo "Flush redis cache"
redis-cli FLUSHALL
echo ""

if [[ "$NORESTART" == "true" ]]; then
  echo "#########################################"
  echo "# RESTART OF SERVICES MANUALLY DISABLED #"
  echo "#########################################"
  echo ""
  echo "Update successfully finished"
  exit 0
fi

OSVERSION=$(grep VERSION_CODENAME /etc/os-release | cut -d= -f2)

if [[ "$NOSYSTEMFILES" == "false" ]]; then
  echo "Copy required system files"
  cp -r ${APPDIR}/system/etc/. /etc/
  cp -r ${APPDIR}/system/lib/. /lib/
  cp -r ${APPDIR}/system/fpm/. /etc/php/${PHPVersion}/fpm/
  cp -r ${APPDIR}/system/usr/. /usr/
  cp ${APPDIR}/system/nginx/ssl_options_$OSVERSION /etc/nginx/openitc/ssl_options.conf
  # only ensure that the files exist
  touch /etc/nginx/openitc/ssl_cert.conf
  touch /etc/nginx/openitc/custom.conf
fi

chmod +x /usr/bin/oitc

echo "Create required system folders"
mkdir -p /opt/openitc/etc/{mysql,grafana,carbon,frontend,nagios,phpnsta,statusengine} /opt/openitc/etc/statusengine/Config

mkdir -p /opt/openitc/logs/frontend/nagios
chown www-data:www-data /opt/openitc/logs/frontend
chown nagios:nagios /opt/openitc/logs/frontend/nagios
chmod 775 /opt/openitc/logs/frontend
chmod 775 /opt/openitc/logs/frontend/nagios

mkdir -p /opt/openitc/frontend/tmp/nagios
chown www-data:www-data /opt/openitc/frontend/tmp
chown nagios:nagios /opt/openitc/frontend/tmp/nagios

mkdir -p /opt/openitc/frontend/webroot/img/charts
chown www-data:www-data /opt/openitc/frontend/webroot/img/charts

if [[ -d /opt/openitc/frontend/plugins/MapModule/webroot/img/ ]]; then
    chown -R www-data:www-data /opt/openitc/frontend/plugins/MapModule/webroot/img/
fi

oitc config_generator_shell --generate

echo "Enable new systemd services"
systemctl daemon-reload
systemctl enable sudo_server.service\
 oitc_cmd.service\
 gearman_worker.service\
 push_notification.service\
 nodejs_server.service\
 openitcockpit-graphing.service\
 oitc_cronjobs.timer

systemctl restart\
 sudo_server.service\
 oitc_cmd.service\
 gearman_worker.service\
 push_notification.service\
 nodejs_server.service\
 oitc_cronjobs.timer

PHPVersion=$(php -r "echo substr(PHP_VERSION, 0, 3);")
echo "Detected PHP Version: ${PHPVersion} try to restart php-fpm"

# Restart services if they are running
# Nagios/Naemon is aliased, so it works for both
for srv in openitcockpit-graphing.service statusengine.service nagios.service nginx.service phpnsta.service supervisor.service; do
  if systemctl is-active --quiet $srv; then
    echo "Restart service: $srv"
    systemctl restart $srv
  fi
done

systemctl is-enabled --quiet php${PHPVersion}-fpm.service
RC=$?
if [ $RC -eq 0 ]; then
  #Is it php7.3-fpm-service ?
  systemctl restart php${PHPVersion}-fpm.service
else
  # Is it just php-fpm.service?
  systemctl is-enabled --quiet php-fpm.service
  RC=$?
  if [ $RC -eq 0 ]; then
    systemctl restart php-fpm.service
  else
    echo "ERROR: could not detect php-fpm systemd service file. You need to restart php-fpm manualy"
  fi
fi

if [ -d "${APPDIR}/plugins/SnmpTrapModule/src" ]; then
    echo "Detected SnmpTrapModule: try to restart snmptrapd and snmptt"
    systemctl enable snmptrapd.service snmptt.service
    systemctl restart snmptrapd.service snmptt.service
fi
