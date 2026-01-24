export function reportDailyLeads(leads) {
    return `*LAPORAN LEADS HARIAN ${leads.date}*

Nama   : ${leads.name}
Cabang : ${leads.branch}

--------------------
AKTIVITAS HARI INI
Leads   : ${leads.leads}
Closing : ${leads.closing}

--------------------
FOLLOW UP

Kemarin
- Leads   : ${leads.fu_yesterday}
- Closing : ${leads.fu_yesterday_closing}

Kemarinnya
- Leads   : ${leads.fu_before_yesterday}
- Closing : ${leads.fu_before_yesterday_closing}

Minggu Lalu
- Leads   : ${leads.fu_last_week}
- Closing : ${leads.fu_last_week_closing}

PELANGGAN LAMA
- Engage  : ${leads.engage_old_customer}
- Closing : ${leads.engage_closing}

Terima kasih`;
}
