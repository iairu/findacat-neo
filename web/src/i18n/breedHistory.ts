/** Short historic origin of each breed, by breed code: [English, Slovak]. */
const pair = (en: string, sk: string): [string, string] => [en, sk];

const curl = pair(
  'A natural mutation: in 1981 a stray longhaired kitten with curled-back ears, Shulamith, was found in Lakewood, California, and became the foundation of the breed.',
  'Prirodzená mutácia: v roku 1981 našli v kalifornskom Lakewoode túlavé dlhosrsté mača s dozadu stočenými ušami, Shulamith, ktoré sa stalo základom plemena.',
);
const burmilla = pair(
  'Born from an accident in London in 1981, when a chinchilla Persian male met a lilac Burmese female; the silver-shaded kittens were developed into a breed of their own.',
  'Vzniklo náhodou v Londýne v roku 1981, keď sa stretol strieborný perzský kocúr s lilovou barmskou mačkou; strieborne tieňované mačiatka sa stali základom samostatného plemena.',
);
const kurilian = pair(
  'A natural bobtail of the Kuril Islands and Kamchatka in the Russian Far East, kept there for centuries; breeding in Russia began in the 1960s and the standard was set in the 1990s.',
  'Prirodzene krátkochvostá mačka z Kurilských ostrovov a Kamčatky na ruskom Ďalekom východe; na plemennú chovnosť sa začala využívať v 60. rokoch 20. storočia a štandard vznikol v 90. rokoch.',
);
const laperm = pair(
  'Began in 1982 on an Oregon farm, where a barn cat gave birth to a bald kitten that grew a soft, curly coat; the dominant curl gene was then bred into the LaPerm.',
  'Vznikla v roku 1982 na farme v Oregone, kde sa domácej mačke narodilo holé mača, ktorému narástla jemná kučeravá srsť; dominantný gén kučier sa potom stal základom plemena.',
);
const oriental = pair(
  'Created in Britain and the United States from the 1950s by crossing Siamese with other short-haired cats, to give the Siamese body type in many more colours and patterns.',
  'Vzniklo v Británii a v USA od 50. rokov 20. storočia krížením siamských mačiek s inými krátkosrstými mačkami, aby sa siamská stavba tela objavila v oveľa viacerých farbách a kresbách.',
);
const selkirk = pair(
  'A 1987 discovery in Montana, where a curly-coated kitten born in an animal shelter was bred to a Persian, starting a plush, curly-coated breed named after the Selkirk Mountains.',
  'Objav z roku 1987 v Montane: kučeravé mača z útulku sa pripustilo k perzskému kocúrovi, čím vzniklo plyšové kučeravé plemeno pomenované podľa pohoria Selkirk.',
);

export const breedHistory: Record<string, [string, string]> = {
  ABY: pair('Developed in Britain in the late 19th century and named after Abyssinia (Ethiopia), where an early cat was said to come from; genetic studies point rather to the coast of the Indian Ocean.',
    'Vyšľachtená v Británii koncom 19. storočia a pomenovaná podľa Habeše (Etiópie), odkiaľ mala údajne pochádzať jedna z prvých mačiek; genetické štúdie ukazujú skôr na pobrežie Indického oceánu.'),
  ACL: curl,
  ACS: curl,
  BAL: pair('A long-haired Siamese: the coat appeared spontaneously in Siamese litters in the United States, and breeders turned it into a breed in the 1950s, naming it after Balinese temple dancers.',
    'Dlhosrstá siamská mačka: dlhá srsť sa spontánne objavila vo vrhoch siamských mačiek v USA a chovatelia z nej v 50. rokoch vytvorili plemeno pomenované podľa balijských chrámových tanečníc.'),
  BEN: pair('Created in California in the 1960s by Jean Mill, who crossed Asian leopard cats with domestic cats; the wild-looking, spotted breed gained recognition in the 1980s.',
    'Vznikol v Kalifornii v 60. rokoch vďaka Jean Millovej, ktorá krížila ázijské leopardie mačky s domácimi; plemeno s divokým škvrnitým vzhľadom bolo uznané v 80. rokoch.'),
  BLH: pair('The long-haired variant of the British Shorthair, which appeared after Persians were used to rebuild the British breed following the world wars.',
    'Dlhosrstý variant britskej krátkosrstej mačky, ktorý sa objavil po zapojení perzských mačiek pri obnove britského plemena po svetových vojnách.'),
  BML: burmilla,
  BMS: burmilla,
  BSH: pair('Britain’s oldest cat breed, descended from street cats and, by tradition, from cats brought by the Romans; shown at the first cat show in 1871 and rebuilt with Persian blood after the world wars.',
    'Najstaršie britské plemeno, pochádzajúce z ulicových mačiek a podľa tradície z mačiek, ktoré priviezli Rimania; predstavilo sa na prvej výstave mačiek v roku 1871 a po svetových vojnách sa obnovilo s pomocou perzskej krvi.'),
  BUR: pair('All modern Burmese descend from Wong Mau, a brown cat brought from Burma to San Francisco in 1930 and bred with Siamese; European lines later grew in a distinct direction.',
    'Všetky moderné barmské mačky pochádzajú z Wong Mau, hnedej mačky, ktorú v roku 1930 priviezli z Barmy do San Francisca a pripúšťali k siamským; európske línie sa neskôr vyvíjali samostatne.'),
  CHA: pair('A blue-grey cat documented in France since the 18th century; legend ties it to the Carthusian monks, and modern breeding took off in Brittany in the 1920s.',
    'Modrosivá mačka doložená vo Francúzsku od 18. storočia; legenda ju spája s kartuziánskymi mníchmi a moderný chov sa rozbehol v Bretónsku v 20. rokoch 20. storočia.'),
  CRX: pair('Began in 1950 on a farm in Cornwall, England, where a curly-coated kitten named Kallibunker was born; the mutation was fixed by crossing him back to his mother.',
    'Vznikol v roku 1950 na farme v anglickom Cornwalle, kde sa narodilo kučeravé mača Kallibunker; mutácia sa upevnila pripustením späť k jeho matke.'),
  CYM: pair('The long-haired version of the Manx, developed mainly in Canada in the 1960s and named with the Welsh word for Wales, Cymru.',
    'Dlhosrstý variant mančinskej mačky, vyšľachtený najmä v Kanade v 60. rokoch 20. storočia a pomenovaný podľa waleského názvu Walesu, Cymru.'),
  DRX: pair('Discovered in 1960 in Buckfastleigh, Devon, where a curly kitten, Kirlee, was born to a stray; its gene proved different from the Cornish Rex, so the two were bred as separate breeds.',
    'Objavený v roku 1960 v Buckfastleigh v Devone, kde sa túlavej mačke narodilo kučeravé mača Kirlee; jeho gén sa ukázal odlišný od cornwallského rexa, preto sa chovajú ako dve samostatné plemená.'),
  DSX: pair('A hairless breed from Rostov-on-Don, Russia, where a hairless kitten found in 1987 passed on a dominant gene, unrelated to the Canadian Sphynx.',
    'Bezsrstné plemeno z ruského Rostova na Done, kde bezsrsté mača nájdené v roku 1987 odovzdalo dominantný gén, nepríbuzný s kanadskou sfingou.'),
  EUR: pair('Descended from the common domestic cats of Europe, spread by Roman settlers; it was defined as a breed in Sweden in the mid-20th century and later recognised internationally.',
    'Pochádza z bežných európskych domácich mačiek, ktoré šírili rímski osadníci; ako plemeno ho vo Švédsku vymedzili v polovici 20. storočia a neskôr získalo medzinárodné uznanie.'),
  EXO: pair('Created in the United States in the 1960s by crossing Persians with American Shorthairs and other short-haired breeds, to get a Persian type with an easy-care short coat.',
    'Vzniklo v USA v 60. rokoch 20. storočia krížením perzských mačiek s americkými krátkosrstými a inými krátkosrstými plemenami s cieľom získať perzský typ s nenáročnou krátkou srsťou.'),
  JBT: pair('Bobtailed cats have lived in Japan for centuries and feature in its folklore and art; the breed reached the West when they were imported to the United States in 1968.',
    'Mačky s krátkym chvostom žijú v Japonsku po stáročia a objavujú sa v jeho folklóre a umení; na Západ sa plemeno dostalo dovozom do USA v roku 1968.'),
  KBL: kurilian,
  KBS: kurilian,
  KOR: pair('An old Thai breed named after the province of Korat (Nakhon Ratchasima) and described in the Tamra Maew, the ancient cat book poems; first exported to the United States in 1959.',
    'Staré thajské plemeno pomenované podľa provincie Korat (Nakhon Ratchasima) a opísané v Tamra Maew, starovekých básňach o mačkách; prvýkrát bolo vyvezené do USA v roku 1959.'),
  LPL: laperm,
  LPS: laperm,
  MAN: pair('Native to the Isle of Man, where a natural mutation produced tailless cats; the island’s isolation kept the gene common for centuries.',
    'Pôvodom z ostrova Man, kde prirodzená mutácia priniesla mačky bez chvosta; izolácia ostrova udržala tento gén bežným po stáročia.'),
  MAU: pair('Spotted cats appear in ancient Egyptian paintings; the modern breed goes back to Princess Natalia Troubetzkoy, who bred Maus in Rome and brought them to the United States in 1956.',
    'Škvrnité mačky sa objavujú na starovekých egyptských maľbách; moderné plemeno siaha k princeznej Natálii Troubetzkoy, ktorá chovala maue v Ríme a v roku 1956 ich priviezla do USA.'),
  MCO: pair('A natural breed of the state of Maine, USA, shaped by harsh winters; popular in the 19th century and shown at Boston in 1895, after which folk tales of its origin multiplied.',
    'Prirodzené plemeno z amerického štátu Maine, vytvarované drsnými zimami; obľúbené bolo v 19. storočí a predstavilo sa v Bostone v roku 1895, po čom sa rozmnožili ľudové príbehy o jeho pôvode.'),
  NEM: pair('Developed in the United States in the 1980s from two long-haired, blue-grey kittens bred by Cora Cobb; its name is German for “creature of the mist”.',
    'Vyšľachtený v USA v 80. rokoch 20. storočia z dvoch dlhosrstých modrosivých mačiat, ktoré choval Cora Cobbová; názov znamená po nemecky „tvor hmly“.'),
  NFO: pair('A natural cat of Norway, found in Norse folklore and bred as a farm and ship cat; a breeding programme in the 1930s was interrupted by the war and revived in the 1970s.',
    'Prirodzená nórska mačka známa zo severskej mytológie, chovaná ako farmárska a lodná mačka; chovateľský program z 30. rokov prerušila vojna a obnovil sa v 70. rokoch.'),
  OCI: pair('Born by chance in Michigan in 1964, when Siamese and Abyssinian lines produced a spotted kitten, Tonga; American Shorthair blood later added size and silver colours.',
    'Vznikol náhodou v Michigane v roku 1964, keď sa z krížencov siamskej a habešskej mačky narodilo škvrnité mača Tonga; krv amerických krátkosrstých mačiek neskôr priniesla veľkosť a strieborné farby.'),
  OLH: oriental,
  OSH: oriental,
  PEB: pair('Founded in 1994 in Saint Petersburg, Russia, by crossing a Don Sphynx male with an Oriental Shorthair female; the dominant hairless gene comes from the Don Sphynx.',
    'Založený v roku 1994 v Petrohrade v Rusku krížením kocúra donskej sfingy s orientálnou krátkosrstou mačkou; dominantný gén bezsrstosti pochádza z donskej sfingy.'),
  PER: pair('Long-haired cats came to Europe from Persia (Iran) and Turkey in the 17th century; Victorian breeders in Britain shaped them into the flat-faced show cat.',
    'Dlhosrsté mačky prišli do Európy z Perzie (Iránu) a Turecka v 17. storočí; britskí viktoriánski chovatelia z nich vytvorili výstavnú mačku s plochou tvárou.'),
  RAG: pair('Created in Riverside, California, in the 1960s by Ann Baker from a white, Angora-type queen named Josephine and her kittens; named for its habit of going limp when held.',
    'Vytvorená v 60. rokoch 20. storočia v Riverside v Kalifornii Ann Bakerovou z bielej mačky angorského typu Josephine a jej mačiat; názov dostala podľa zvyku ochabnúť, keď ju držíte.'),
  RUS: pair('Said to come from the Russian port of Arkhangelsk, brought to Britain and northern Europe by sailors in the 1860s; first shown in London in 1875.',
    'Údajne pochádza z ruského prístavu Archangelsk, odkiaľ ju v 60. rokoch 19. storočia priviezli námorníci do Británie a severnej Európy; prvýkrát sa predstavila v Londýne v roku 1875.'),
  SBI: pair('Surrounded by a legend of Burmese temple cats, the Birman is documented in France from the 1920s and was reconstructed there after the Second World War.',
    'Birma je obklopená legendou o barmských chrámových mačkách; vo Francúzsku je doložená od 20. rokov 20. storočia a po druhej svetovej vojne sa tam obnovila.'),
  SIA: pair('Native to Thailand (Siam) and described in manuscripts such as the Tamra Maew; imported to Britain in the 1880s and shown at the Crystal Palace in 1885.',
    'Pôvodom z Thajska (Siam), opísaná v rukopisoch ako Tamra Maew; do Británie sa dostala v 80. rokoch 19. storočia a v roku 1885 sa predstavila v Crystal Palace.'),
  SIB: pair('The forest cat of Russia, mentioned in records for around a thousand years and bred in Russian clubs from the 1980s before spreading abroad.',
    'Ruská lesná mačka, spomínaná v záznamoch zhruba tisíc rokov; v ruských kluboch sa chová od 80. rokov 20. storočia a odtiaľ sa rozšírila do sveta.'),
  SIN: pair('The street cat of Singapore, taken to the United States in the 1970s by Tommy and Hal Meadow, who developed it into the smallest recognised breed.',
    'Ulicová mačka zo Singapuru, ktorú v 70. rokoch 20. storočia odviezli do USA Tommy a Hal Meadowovci a vyšľachtili z nej najmenšie uznané plemeno.'),
  SNO: pair('Started in Philadelphia in the 1960s, when a breeder noticed Siamese kittens with white feet and crossed them with American Shorthairs.',
    'Vzniklo vo Philadelphii v 60. rokoch 20. storočia, keď si chovateľka všimla siamské mačiatka s bielymi labkami a krížila ich s americkými krátkosrstými.'),
  SOK: pair('From the Arabuko-Sokoke forest on the coast of Kenya, where wild-living cats were found in 1978 and brought to Denmark in 1984 to found the breed.',
    'Z lesa Arabuko-Sokoke na pobreží Kene, kde v roku 1978 našli voľne žijúce mačky; v roku 1984 ich previezli do Dánska, aby založili plemeno.'),
  SOM: pair('The long-haired Abyssinian: a recessive long-hair gene surfaced in Abyssinian litters in the 20th century, and breeders in North America developed it into a breed from the 1960s.',
    'Dlhosrstá habešská mačka: recesívny gén dlhej srsti sa v 20. storočí objavil vo vrhoch habešských mačiek a chovatelia v Severnej Amerike z neho od 60. rokov vytvorili plemeno.'),
  SPH: pair('Began in Toronto in 1966, when a hairless kitten named Prune was born to a domestic cat; the breed was then developed in North America and Europe.',
    'Začala v Toronte v roku 1966, keď sa domácej mačke narodilo bezsrsté mača Prune; plemeno sa potom rozvíjalo v Severnej Amerike a v Európe.'),
  SRL: selkirk,
  SRS: selkirk,
  THA: pair('The traditional, rounder Siamese of Thailand, kept alive by breeders when the show Siamese became more slender; it was recognised as a separate breed in the late 20th century.',
    'Tradičná, zaoblenejšia siamská mačka z Thajska, ktorú chovatelia zachovali, keď sa výstavná siamská mačka stala štíhlejšou; ako samostatné plemeno bola uznaná koncom 20. storočia.'),
  TON: pair('A cross of Siamese and Burmese, descended from Wong Mau; the breed was deliberately developed in Canada and the United States in the 1960s.',
    'Krížence siamskej a barmskej mačky pochádzajúce z Wong Mau; plemeno cielene vznikalo v Kanade a v USA v 60. rokoch 20. storočia.'),
  TUA: pair('From the area around Ankara, Turkey, where long-haired white cats were kept for centuries; introduced to Europe in the 17th century and preserved at Ankara Zoo.',
    'Z okolia tureckej Ankary, kde chovali dlhosrsté biele mačky po stáročia; do Európy sa dostala v 17. storočí a zachovala sa aj v zoologickej záhrade v Ankare.'),
  TUV: pair('A swimming cat from the Lake Van region of eastern Turkey, brought to Britain in 1955 by Laura Lushington and Sonia Halliday.',
    'Plávajúca mačka z oblasti jazera Van vo východnom Turecku, ktorú do Británie v roku 1955 priviezli Laura Lushingtonová a Sonia Hallidayová.'),
  YOR: pair('Developed in the 1980s on a farm in New York State from a black-and-white long-haired barn cat, giving a chocolate or lavender, plush-coated breed.',
    'Vyšľachtená v 80. rokoch 20. storočia na farme v štáte New York z čierno-bielej dlhosrstej farmárskej mačky; vznikla čokoládová alebo levanduľová plyšová mačka.'),
};

/** The description for a breed code in the given language, or null when none is known. */
export const breedStory = (code: string, locale: string): string | null => {
  const entry = breedHistory[code.trim().toUpperCase()];
  return entry ? entry[locale === 'sk' ? 1 : 0] : null;
};
