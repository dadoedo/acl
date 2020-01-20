## ACL ukážka kódu
***
**Navrhněte a naprogramujte ACL systém (systém řízení oprávnění) pro uživatele. S následujícími požadavky:**

Požadavky na ACL
- ACL bude podporovat Role, Zdroje, Akce a Pravidla
- Pravidlo lze definovat jako mezi Zdrojem, Rolí a Akcí jako povoleno nebo zákázáno
- Role lze přiřazovat k uživateli
- Uživatel může být ve více rolích
Příklad: Například Role: editor, Zdroj: článek, Akce: editace, Pravidlo: editor může editovat článek

Výstupem by mělo být:
- Datový model popisujícím Role, Zdroje, Akce, Pravidla a Uživatele
- Service třída umožňující autorizaci vůči tomuto ACL
- Testy na kusy kódu, které uznáte za vhodné, že by měli být otestované
***
##### TODO / Ideas
* zhodnotiť Interface voči ACL service
* premyslieť voting strategy
* pridať akcie do ACL service
            
