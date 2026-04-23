#!/bin/sh
# PHP-FPM läuft als uid 1000 ("application"). Nur `group_add` im Compose reicht oft nicht,
# weil FPM die Zusatzgruppen des Container-Starts nicht übernimmt. Hier: gleiche GID wie
# /var/run/docker.sock in /etc/group abbilden und den App-User dieser Gruppe zuordnen.
[ -S /var/run/docker.sock ] || exit 0

GID=$(stat -c '%g' /var/run/docker.sock 2>/dev/null) || exit 0
[ -n "$GID" ] || exit 0

APP_USER=$(awk -F: '$3==1000 {print $1; exit}' /etc/passwd)
[ -n "$APP_USER" ] || exit 0

GNAME=$(awk -F: -v gid="$GID" '$3 == gid {print $1; exit}' /etc/group)
if [ -z "$GNAME" ]; then
    addgroup -g "$GID" -S dockersock 2>/dev/null || true
    GNAME=$(awk -F: -v gid="$GID" '$3 == gid {print $1; exit}' /etc/group)
fi
[ -n "$GNAME" ] || exit 0

adduser "$APP_USER" "$GNAME" 2>/dev/null || true
